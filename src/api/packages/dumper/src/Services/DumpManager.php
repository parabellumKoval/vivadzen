<?php

namespace ParabellumKoval\Dumper\Services;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\DatabaseManager;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ParabellumKoval\Dumper\Data\DumpRecord;
use ParabellumKoval\Dumper\Support\AutoDumpSchedulePresets;
use PDO;
use RuntimeException;
use Spatie\DbDumper\Databases\MySql;
use Symfony\Component\Process\Process;
use Throwable;

class DumpManager
{
    protected const MAX_DUMP_ATTEMPTS = 3;

    protected string $connectionName;

    /**
     * @var array<string, mixed>
     */
    protected array $connectionConfig;

    protected string $metaExtension;

    protected string $mysqldumpBinary = 'mysqldump';

    protected ?bool $mysqldumpSupportsColumnStatistics = null;

    /**
     * @var array{raw: string, version: string|null, is_mariadb: bool}|null
     */
    protected ?array $mysqldumpClientInfo = null;

    /**
     * @var array{raw: string, version: string|null, is_mariadb: bool}|null
     */
    protected ?array $mysqlClientInfo = null;

    /**
     * @var array<string, string>|null
     */
    protected ?array $tableSlugMap = null;

    public function __construct(
        protected FilesystemFactory $filesystem,
        protected DatabaseManager $database,
        protected Repository $config,
        protected TableInspector $inspector,
        protected DumperSettings $settings,
        protected RemoteDumpManager $remoteManager
    ) {
        $this->connectionName = (string) $this->settings->value(
            'dumper.connection',
            $this->inspector->connectionName()
        );
        $this->connectionConfig = $this->config->get("database.connections.{$this->connectionName}", []);
        $this->metaExtension = (string) $this->config->get('dumper.metadata_extension', '.meta.json');
    }

    public function manualDumps(): Collection
    {
        $manual = $this->manualConfig();

        return $this->scanDirectory(
            $manual['disk'],
            $manual['directory'],
            'manual',
            null
        );
    }

    public function autoDumps(?string $caseKey = null): Collection
    {
        if ($caseKey) {
            $case = $this->autoCases()[$caseKey] ?? null;
            if (!$case) {
                return collect();
            }

            return $this->scanDirectory(
                $case['disk'],
                $case['directory'],
                'auto',
                $caseKey
            );
        }

        return collect($this->autoCases())
            ->flatMap(fn (array $case) => $this->scanDirectory($case['disk'], $case['directory'], 'auto', $case['key'])->all())
            ->sortByDesc(fn (DumpRecord $record) => $record->createdAt);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function autoCases(): array
    {
        $auto = $this->settings->group('dumper.auto', (array) $this->config->get('dumper.auto', []));
        $cases = $this->configuredAutoCases();
        $baseDirectory = $this->normalizeDirectory((string) ($auto['base_directory'] ?? 'dumps/auto'));
        $baseDisk = (string) ($auto['disk'] ?? $this->manualConfig()['disk']);
        $prefix = (string) ($auto['filename_prefix'] ?? 'auto');

        $normalized = [];

        foreach ($cases as $key => $definition) {
            $definition = (array) $definition;
            $scheduleKey = trim((string) ($definition['schedule'] ?? ''));
            $cron = AutoDumpSchedulePresets::cronFor($scheduleKey);

            if ($cron === null) {
                $cron = trim((string) ($definition['cron'] ?? ''));
                $cron = $cron !== '' ? $cron : null;
            }

            if ($scheduleKey === '') {
                $scheduleKey = AutoDumpSchedulePresets::keyForCron($cron);
            }

            $directory = $this->normalizeDirectory($definition['directory'] ?? $definition['path'] ?? $key);
            if ($directory === '') {
                $directory = $this->normalizeDirectory((string) $key);
            }

            $fullDirectory = $directory;
            if ($baseDirectory !== '') {
                $fullDirectory = $baseDirectory . '/' . ltrim($directory, '/');
            }

            $normalized[$key] = [
                'key' => (string) $key,
                'label' => (string) ($definition['label'] ?? Str::headline((string) $key)),
                'tables' => $this->normalizeAutoCaseTables($definition['tables[]'] ?? $definition['tables'] ?? '*'),
                'schedule' => $scheduleKey !== '' ? $scheduleKey : null,
                'schedule_label' => AutoDumpSchedulePresets::labelFor($scheduleKey),
                'cron' => $cron,
                'description' => ($definition['description'] ?? null) !== null ? (string) $definition['description'] : null,
                'disk' => $definition['disk'] ?? $baseDisk,
                'directory' => $fullDirectory,
                'retention' => $this->normalizeAutoCaseRetention($definition),
                'filename_prefix' => $definition['filename_prefix'] ?? $prefix,
                'sync_to_remote' => $this->normalizeBooleanConfigValue($definition['sync_to_remote'] ?? false) ?? false,
            ];
        }

        return $normalized;
    }

    public function createManualDump(?array $tables, ?string $label = null): DumpRecord
    {
        $manual = $this->manualConfig();

        $record = $this->createDump(
            $manual['disk'],
            $manual['directory'],
            $manual['filename_prefix'],
            'manual',
            null,
            $this->normalizeTablesInput($tables),
            $label
        );

        if (!empty($manual['sync_to_remote'])) {
            $this->remoteManager->dispatchUpload($record);
        }

        return $record;
    }

    public function createAutoDump(string $caseKey): ?DumpRecord
    {
        $cases = $this->autoCases();
        $case = $cases[$caseKey] ?? null;

        if (!$case) {
            return null;
        }

        $record = $this->createDump(
            $case['disk'],
            $case['directory'],
            $case['filename_prefix'],
            'auto',
            $caseKey,
            $this->normalizeTablesConfig($case['tables']),
            $case['label'] ?? null
        );

        if (!empty($case['sync_to_remote'])) {
            $this->remoteManager->dispatchUpload($record);
        }

        $this->applyRetention($caseKey, $case);

        return $record;
    }

    public function resolveReference(string $reference): ?DumpRecord
    {
        $decoded = $this->decodeReference($reference);

        if (!is_array($decoded)) {
            return null;
        }

        $disk = $decoded['disk'] ?? null;
        $path = $decoded['path'] ?? null;

        if (!$disk || !$path) {
            return null;
        }

        if (!$this->isAllowedPath($disk, $path)) {
            return null;
        }

        return $this->buildRecord($disk, $path);
    }

    public function restore(DumpRecord $record): void
    {
        $adapter = $this->filesystem->disk($record->disk);

        if (!$adapter instanceof FilesystemAdapter || !method_exists($adapter, 'path')) {
            throw new RuntimeException('Restore is only supported for local filesystems.');
        }

        $filePath = $adapter->path($record->path);
        $options = [
            'disable_ssl' => $this->shouldDisableSslByDefault(),
        ];

        for ($attempt = 1; $attempt <= self::MAX_DUMP_ATTEMPTS; $attempt++) {
            $stream = fopen($filePath, 'rb');

            if ($stream === false) {
                throw new RuntimeException('Unable to read dump file for restore.');
            }

            $process = new Process($this->buildMysqlCommand($options));
            $process->setTimeout(null);

            try {
                $process->setInput($stream);
                $process->mustRun();

                return;
            } catch (Throwable $exception) {
                $shouldRetryWithSslDisabled = !$options['disable_ssl']
                    && $this->isSslFailure($exception)
                    && $this->canDisableMysqlSsl();

                if (!$shouldRetryWithSslDisabled || $attempt === self::MAX_DUMP_ATTEMPTS) {
                    throw $exception;
                }

                $options['disable_ssl'] = true;
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }
    }

    public function delete(DumpRecord $record): void
    {
        $adapter = $this->filesystem->disk($record->disk);

        if ($this->shouldDispatchRemoteDelete($record)) {
            $this->remoteManager->dispatchDelete($record);
        }

        $adapter->delete($record->path);
        $adapter->delete($this->metaPath($record->path));
    }

    public function resolveStoredDump(string $disk, string $path): ?DumpRecord
    {
        if (!$this->isAllowedPath($disk, $path)) {
            return null;
        }

        return $this->buildRecord($disk, $path);
    }

    /**
     * @param array<int, string> $providers
     */
    public function markRemoteProviders(DumpRecord $record, array $providers): DumpRecord
    {
        return $this->writeRemoteProvidersMetadata($record, $providers);
    }

    public function groupedAutoDumps(): array
    {
        $cases = $this->autoCases();
        $grouped = [];

        foreach ($cases as $key => $case) {
            $grouped[$key] = $this->autoDumps($key);
        }

        return $grouped;
    }

    /**
     * @return array{text: string, value: string}
     */
    public function tableOptions(): array
    {
        return array_map(fn (string $table) => ['text' => $table, 'value' => $table], $this->inspector->tables());
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function configuredAutoCases(): array
    {
        $cases = $this->normalizeAutoCaseDefinitions($this->settings->value('dumper.auto.cases'));
        $customized = $this->normalizeBooleanConfigValue($this->settings->value('dumper.auto.cases_customized', null)) ?? false;

        if ($customized || $cases !== []) {
            return $cases;
        }

        return $this->normalizeAutoCaseDefinitions($this->config->get('dumper.auto.cases', []));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function normalizeAutoCaseDefinitions(mixed $cases): array
    {
        if (!is_array($cases)) {
            return [];
        }

        $normalized = [];
        $usedKeys = [];

        foreach ($cases as $index => $definition) {
            if (!is_array($definition)) {
                continue;
            }

            $label = trim((string) ($definition['label'] ?? ''));
            if ($label === '' && is_string($index)) {
                $label = Str::headline($index);
            }

            if ($label === '') {
                continue;
            }

            $candidateKey = trim((string) ($definition['key'] ?? ''));
            if ($candidateKey === '' && is_string($index)) {
                $candidateKey = $index;
            }

            $key = $this->uniqueAutoCaseKey($candidateKey, $label, $usedKeys);
            $definition['label'] = $label;
            $definition['key'] = $key;
            $normalized[$key] = $definition;
        }

        return $normalized;
    }

    /**
     * @param array<string, bool> $usedKeys
     */
    protected function uniqueAutoCaseKey(string $candidateKey, string $label, array &$usedKeys): string
    {
        $baseKey = Str::slug($candidateKey !== '' ? $candidateKey : $label, '_');
        if ($baseKey === '') {
            $baseKey = 'auto_case';
        }

        $key = $baseKey;
        $suffix = 2;

        while (isset($usedKeys[$key])) {
            $key = $baseKey . '_' . $suffix;
            $suffix++;
        }

        $usedKeys[$key] = true;

        return $key;
    }

    /**
     * @return array<int, string>|string
     */
    protected function normalizeAutoCaseTables(mixed $tables): array|string
    {
        if ($tables === null || $tables === '' || $tables === '*' || $tables === ['*']) {
            return '*';
        }

        if (is_string($tables)) {
            $tables = array_map('trim', explode(',', $tables));
        }

        $tables = array_values(array_filter(array_map(
            static fn (mixed $table): string => trim((string) $table),
            (array) $tables
        )));

        if ($tables === [] || in_array('__all__', $tables, true) || in_array('*', $tables, true)) {
            return '*';
        }

        return array_values(array_unique($tables));
    }

    /**
     * @param array<string, mixed> $definition
     * @return array{keep_last: int|null, keep_days: int|null}
     */
    protected function normalizeAutoCaseRetention(array $definition): array
    {
        $retention = (array) ($definition['retention'] ?? []);

        return [
            'keep_last' => $this->normalizeRetentionValue($definition['retention_keep_last'] ?? $retention['keep_last'] ?? null),
            'keep_days' => $this->normalizeRetentionValue($definition['retention_keep_days'] ?? $retention['keep_days'] ?? null),
        ];
    }

    protected function normalizeRetentionValue(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return max(0, (int) $value);
    }

    protected function createDump(
        string $disk,
        string $directory,
        string $prefix,
        string $source,
        ?string $caseKey,
        ?array $tables,
        ?string $label
    ): DumpRecord {
        $disk = (string) $disk;
        $directory = $this->normalizeDirectory($directory);
        $prefix = trim($prefix) !== '' ? trim($prefix) : $source;

        $adapter = $this->filesystem->disk($disk);
        $now = CarbonImmutable::now();
        $tablesSlug = $this->tablesSlug($tables);
        $filename = sprintf('%s_%s__%s.sql', $prefix, $now->format('Ymd_His'), $tablesSlug);
        $relativePath = $directory !== '' ? $directory . '/' . $filename : $filename;

        if ($directory !== '') {
            $adapter->makeDirectory($directory);
        }

        $absolutePath = $this->absolutePath($adapter, $relativePath);
        $this->dumpToFile($absolutePath, $tables);

        $meta = [
            'label' => $label,
            'tables' => $tables ?? ['*'],
            'created_at' => $now->toIso8601String(),
            'source' => $source,
            'case' => $caseKey,
            'database' => $this->connectionConfig['database'] ?? null,
            'connection' => $this->connectionName,
        ];

        $adapter->put($this->metaPath($relativePath), json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $record = $this->buildRecord($disk, $relativePath);

        if (!$record) {
            throw new RuntimeException('Dump file was created but is invalid or empty.');
        }

        return $record;
    }

    protected function scanDirectory(string $disk, string $directory, string $source, ?string $caseKey): Collection
    {
        $adapter = $this->filesystem->disk($disk);
        $directory = $this->normalizeDirectory($directory);

        if ($directory !== '' && !$adapter->exists($directory)) {
            return collect();
        }

        $files = $directory === '' ? $adapter->allFiles() : $adapter->allFiles($directory);

        return collect($files)
            ->filter(fn (string $path) => Str::endsWith($path, '.sql'))
            ->map(fn (string $path) => $this->buildRecord($disk, $path, $source, $caseKey))
            ->filter()
            ->sortByDesc(fn (DumpRecord $record) => $record->createdAt)
            ->values();
    }

    protected function buildRecord(string $disk, string $path, ?string $source = null, ?string $caseKey = null): ?DumpRecord
    {
        $adapter = $this->filesystem->disk($disk);

        if (!$adapter->exists($path)) {
            return null;
        }

        $size = (int) $adapter->size($path);
        if ($size <= 0) {
            return null;
        }

        $meta = [];
        $metaPath = $this->metaPath($path);

        if ($adapter->exists($metaPath)) {
            $decoded = json_decode($adapter->get($metaPath), true);
            if (is_array($decoded)) {
                $meta = $decoded;
            }
        }

        $createdAt = $meta['created_at'] ?? CarbonImmutable::createFromTimestamp($adapter->lastModified($path))->toIso8601String();
        $tables = $meta['tables'] ?? null;
        if (!is_array($tables) || $tables === [] || (count($tables) === 1 && $tables[0] === '*')) {
            $tables = $this->guessTablesFromFilename(basename($path)) ?? ['*'];
        } else {
            $tables = array_values($tables);
        }

        return new DumpRecord(
            $disk,
            $path,
            basename($path),
            CarbonImmutable::parse($createdAt),
            $tables,
            $meta['source'] ?? $source ?? 'manual',
            $meta['case'] ?? $caseKey,
            $meta['label'] ?? null,
            $size,
            $this->normalizeRemoteProviders($meta['remote_providers'] ?? [])
        );
    }

    protected function tablesSlug(?array $tables): string
    {
        if ($tables === null) {
            return 'full';
        }

        $list = array_slice($tables, 0, 4);
        $slug = Str::slug(implode('-', $list));

        if (count($tables) > 4) {
            $slug .= '-plus';
        }

        return $slug !== '' ? $slug : 'partial';
    }

    protected function normalizeTablesInput(?array $tables): ?array
    {
        if (!$tables) {
            return null;
        }

        $tables = array_filter(array_map(fn ($table) => trim((string) $table), $tables));

        if ($tables === [] || in_array('__all__', $tables, true) || in_array('*', $tables, true)) {
            return null;
        }

        return array_values(array_unique($tables));
    }

    protected function normalizeTablesConfig(mixed $tables): ?array
    {
        if ($tables === null || $tables === '*' || $tables === ['*']) {
            return null;
        }

        if (is_string($tables)) {
            $tables = array_map('trim', explode(',', $tables));
        }

        $tables = array_filter(array_map(fn ($table) => trim((string) $table), (array) $tables));

        return $tables === [] ? null : array_values(array_unique($tables));
    }

    protected function manualConfig(): array
    {
        $manual = $this->settings->group('dumper.manual', (array) $this->config->get('dumper.manual', []));

        return [
            'disk' => $manual['disk'] ?? 'local',
            'directory' => $manual['directory'] ?? 'dumps/manual',
            'filename_prefix' => $manual['filename_prefix'] ?? 'manual',
            'sync_to_remote' => $this->normalizeBooleanConfigValue($manual['sync_to_remote'] ?? false) ?? false,
        ];
    }

    protected function shouldDispatchRemoteDelete(DumpRecord $record): bool
    {
        if ($record->remoteProviders !== []) {
            return true;
        }

        if ($record->isAuto()) {
            return true;
        }

        $manual = $this->manualConfig();

        return !empty($manual['sync_to_remote']);
    }

    protected function normalizeDirectory(?string $directory): string
    {
        $directory = trim((string) $directory);
        $directory = str_replace('..', '', $directory);

        return trim($directory, '/');
    }

    protected function isAllowedPath(string $disk, string $path): bool
    {
        if (str_contains($path, '..')) {
            return false;
        }

        $directories = $this->allowedDirectories()[$disk] ?? [];
        $path = ltrim($path, '/');

        if ($directories === []) {
            return false;
        }

        foreach ($directories as $directory) {
            if ($directory === '') {
                return true;
            }

            if (Str::startsWith($path, $directory)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function allowedDirectories(): array
    {
        $directories = [];

        $manual = $this->manualConfig();
        $directories[$manual['disk']][] = $this->normalizeDirectory($manual['directory']);

        foreach ($this->autoCases() as $case) {
            $directories[$case['disk']][] = $this->normalizeDirectory($case['directory']);
        }

        foreach ($directories as $disk => $paths) {
            $directories[$disk] = array_values(array_unique(array_filter($paths, fn ($path) => $path !== '/')));
        }

        return $directories;
    }

    protected function metaPath(string $path): string
    {
        return $path . $this->metaExtension;
    }

    protected function absolutePath(Filesystem $filesystem, string $path): string
    {
        if ($filesystem instanceof FilesystemAdapter && method_exists($filesystem, 'path')) {
            return $filesystem->path($path);
        }

        throw new RuntimeException('Filesystem does not expose local path for dumps.');
    }

    protected function dumpToFile(string $absolutePath, ?array $tables): void
    {
        $options = [
            'disable_column_statistics' => $this->shouldDisableColumnStatistics(),
            'disable_ssl' => $this->shouldDisableSslByDefault(),
        ];

        $lastException = null;

        for ($attempt = 1; $attempt <= self::MAX_DUMP_ATTEMPTS; $attempt++) {
            try {
                $this->buildDumper($tables, $options)->dumpToFile($absolutePath);

                return;
            } catch (Throwable $exception) {
                $lastException = $exception;
                $this->deleteFileIfExists($absolutePath);

                $shouldRetry = false;

                if ($options['disable_column_statistics'] && $this->isColumnStatisticsFailure($exception)) {
                    $options['disable_column_statistics'] = false;
                    $shouldRetry = true;
                }

                if (!$options['disable_ssl'] && $this->isSslFailure($exception) && $this->canDisableMysqldumpSsl()) {
                    $options['disable_ssl'] = true;
                    $shouldRetry = true;
                }

                if (!$shouldRetry || $attempt === self::MAX_DUMP_ATTEMPTS) {
                    throw $exception;
                }
            }
        }

        if ($lastException) {
            throw $lastException;
        }
    }

    protected function buildDumper(?array $tables, array $options = []): MySql
    {
        $config = $this->connectionConfig;

        if (($config['driver'] ?? 'mysql') !== 'mysql') {
            throw new RuntimeException('Dumper supports only MySQL connections.');
        }

        $dumper = MySql::create()
            ->setDbName($config['database'] ?? '')
            ->setHost($config['host'] ?? '127.0.0.1')
            ->setPort((int) ($config['port'] ?? 3306))
            ->setUserName($config['username'] ?? $config['user'] ?? '')
            ->setPassword($config['password'] ?? '');

        if (!empty($config['unix_socket'])) {
            $dumper->setSocket($config['unix_socket']);
        }

        $dumper->skipLockTables();
        $dumper->useExtendedInserts();
        $this->configureMysqldumpBinary($dumper);
        $this->configureSslOptions($dumper);
        $this->configureTablespaceOptions($dumper);

        if (($options['disable_ssl'] ?? false) === true) {
            $this->disableMysqldumpSsl($dumper);
        }

        if (($options['disable_column_statistics'] ?? false) === true && $this->mysqldumpSupportsColumnStatisticsOption()) {
            $dumper->doNotUseColumnStatistics();
        }

        if ($tables !== null) {
            $dumper->includeTables($tables);
        }

        $dumper->useSingleTransaction();

        return $dumper;
    }

    protected function buildMysqlCommand(array $options = []): array
    {
        $config = $this->connectionConfig;
        $binary = trim((string) $this->settings->value(
            'dumper.binaries.mysql',
            $this->config->get('dumper.binaries.mysql', 'mysql')
        ));

        $command = [$binary !== '' ? $binary : 'mysql'];

        $username = $config['username'] ?? $config['user'] ?? null;
        if ($username) {
            $command[] = '--user=' . $username;
        }

        if (!empty($config['password'])) {
            $command[] = '--password=' . $config['password'];
        }

        if (!empty($config['host'])) {
            $command[] = '--host=' . $config['host'];
        }

        if (!empty($config['port'])) {
            $command[] = '--port=' . $config['port'];
        }

        if (!empty($config['unix_socket'])) {
            $command[] = '--socket=' . $config['unix_socket'];
        }

        if (($options['disable_ssl'] ?? false) === true) {
            $command[] = $this->mysqlDisableSslOption();
        }

        $command[] = $config['database'] ?? '';

        return $command;
    }

    protected function configureMysqldumpBinary(MySql $dumper): void
    {
        $binaryPath = trim((string) $this->settings->value(
            'dumper.binaries.mysqldump',
            $this->config->get('dumper.binaries.mysqldump', '')
        ));
        $binary = 'mysqldump';

        if ($binaryPath !== '') {
            $binaryPath = rtrim($binaryPath, '/');
            $dumper->setDumpBinaryPath($binaryPath . '/');
            $binary = $binaryPath . '/mysqldump';
        }

        if ($this->mysqldumpBinary !== $binary) {
            $this->mysqldumpBinary = $binary;
            $this->mysqldumpSupportsColumnStatistics = null;
            $this->mysqldumpClientInfo = null;
        }
    }

    protected function shouldDisableColumnStatistics(): bool
    {
        $configured = $this->normalizeBooleanConfigValue($this->settings->value(
            'dumper.options.disable_column_statistics',
            $this->config->get('dumper.options.disable_column_statistics')
        ));

        if ($configured !== null) {
            return $configured && $this->mysqldumpSupportsColumnStatisticsOption();
        }

        return $this->mysqldumpSupportsColumnStatisticsOption();
    }

    protected function shouldDisableSslByDefault(): bool
    {
        $configured = $this->normalizeBooleanConfigValue($this->settings->value(
            'dumper.options.disable_ssl',
            $this->config->get('dumper.options.disable_ssl')
        ));

        return $configured ?? false;
    }

    protected function mysqldumpSupportsColumnStatisticsOption(): bool
    {
        if ($this->mysqldumpSupportsColumnStatistics !== null) {
            return $this->mysqldumpSupportsColumnStatistics;
        }

        $clientInfo = $this->mysqldumpClientInfo();

        if ($clientInfo['is_mariadb']) {
            return $this->mysqldumpSupportsColumnStatistics = false;
        }

        if ($clientInfo['version'] === null) {
            return $this->mysqldumpSupportsColumnStatistics = false;
        }

        return $this->mysqldumpSupportsColumnStatistics = version_compare($clientInfo['version'], '8.0.0', '>=');
    }

    protected function decodeReference(string $reference): ?array
    {
        $reference = strtr($reference, '-_', '+/');
        $padding = strlen($reference) % 4;
        if ($padding > 0) {
            $reference .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($reference, true);

        if ($decoded === false) {
            return null;
        }

        $payload = json_decode($decoded, true);

        return is_array($payload) ? $payload : null;
    }

    protected function applyRetention(string $caseKey, array $case): void
    {
        $retention = $case['retention'] ?? [];
        $keepLast = isset($retention['keep_last']) ? (int) $retention['keep_last'] : null;
        $keepDays = isset($retention['keep_days']) ? (int) $retention['keep_days'] : null;

        if (!$keepLast && !$keepDays) {
            return;
        }

        $records = $this->autoDumps($caseKey);

        if ($keepDays && $keepDays > 0) {
            $threshold = CarbonImmutable::now()->subDays($keepDays);
            $records
                ->filter(fn (DumpRecord $record) => $record->createdAt->lt($threshold))
                ->each(fn (DumpRecord $record) => $this->delete($record));

            $records = $this->autoDumps($caseKey);
        }

        if ($keepLast && $keepLast > 0) {
            $records
                ->sortByDesc(fn (DumpRecord $record) => $record->createdAt)
                ->slice($keepLast)
                ->each(fn (DumpRecord $record) => $this->delete($record));
        }
    }

    protected function configureSslOptions(MySql $dumper): void
    {
        $options = [];
        if (isset($this->connectionConfig['options']) && is_array($this->connectionConfig['options'])) {
            $options = $this->connectionConfig['options'];
        }

        $mappings = [
            [
                'pdo' => defined('PDO::MYSQL_ATTR_SSL_CA') ? PDO::MYSQL_ATTR_SSL_CA : null,
                'config' => ['ssl_ca', 'sslca', 'ssl-ca'],
                'flag' => '--ssl-ca=%s',
            ],
            [
                'pdo' => defined('PDO::MYSQL_ATTR_SSL_CERT') ? PDO::MYSQL_ATTR_SSL_CERT : null,
                'config' => ['ssl_cert', 'sslcert', 'ssl-cert'],
                'flag' => '--ssl-cert=%s',
            ],
            [
                'pdo' => defined('PDO::MYSQL_ATTR_SSL_KEY') ? PDO::MYSQL_ATTR_SSL_KEY : null,
                'config' => ['ssl_key', 'sslkey', 'ssl-key'],
                'flag' => '--ssl-key=%s',
            ],
            [
                'pdo' => defined('PDO::MYSQL_ATTR_SSL_CAPATH') ? PDO::MYSQL_ATTR_SSL_CAPATH : null,
                'config' => ['ssl_capath', 'sslcapath', 'ssl-capath'],
                'flag' => '--ssl-capath=%s',
            ],
            [
                'pdo' => defined('PDO::MYSQL_ATTR_SSL_CIPHER') ? PDO::MYSQL_ATTR_SSL_CIPHER : null,
                'config' => ['ssl_cipher', 'sslcipher', 'ssl-cipher'],
                'flag' => '--ssl-cipher=%s',
            ],
        ];

        foreach ($mappings as $mapping) {
            $value = $this->connectionOptionValue($options, $mapping['pdo'], $mapping['config']);
            if ($value !== null) {
                $dumper->addExtraOption(sprintf($mapping['flag'], escapeshellarg($value)));
            }
        }

        $sslMode = $this->connectionConfig['sslmode'] ?? $this->connectionConfig['ssl_mode'] ?? $this->connectionConfig['ssl-mode'] ?? null;
        if (is_string($sslMode)) {
            $sslMode = trim($sslMode);
        }

        if (is_string($sslMode) && $sslMode !== '') {
            $dumper->addExtraOption('--ssl-mode=' . escapeshellarg($sslMode));
        }
    }

    protected function configureTablespaceOptions(MySql $dumper): void
    {
        $noTablespaces = $this->normalizeBooleanConfigValue($this->settings->value(
            'dumper.options.no_tablespaces',
            $this->config->get('dumper.options.no_tablespaces')
        ));

        if ($noTablespaces ?? true) {
            $dumper->addExtraOption('--no-tablespaces');
        }
    }

    protected function disableMysqldumpSsl(MySql $dumper): void
    {
        $dumper->setSkipSsl();
        $dumper->setSslFlag(ltrim($this->mysqldumpDisableSslOption(), '-'));
    }

    protected function mysqldumpDisableSslOption(): string
    {
        return $this->disableSslOptionForClient($this->mysqldumpClientInfo());
    }

    protected function mysqlDisableSslOption(): string
    {
        return $this->disableSslOptionForClient($this->mysqlClientInfo());
    }

    protected function canDisableMysqldumpSsl(): bool
    {
        return $this->mysqldumpDisableSslOption() !== '';
    }

    protected function canDisableMysqlSsl(): bool
    {
        return $this->mysqlDisableSslOption() !== '';
    }

    protected function disableSslOptionForClient(array $clientInfo): string
    {
        if ($clientInfo['is_mariadb']) {
            return '--skip-ssl';
        }

        if ($clientInfo['version'] !== null && version_compare($clientInfo['version'], '8.4.0', '>=')) {
            return '--ssl-mode=DISABLED';
        }

        return '--skip-ssl';
    }

    /**
     * @return array{raw: string, version: string|null, is_mariadb: bool}
     */
    protected function mysqldumpClientInfo(): array
    {
        if ($this->mysqldumpClientInfo !== null) {
            return $this->mysqldumpClientInfo;
        }

        return $this->mysqldumpClientInfo = $this->binaryClientInfo($this->mysqldumpBinary);
    }

    /**
     * @return array{raw: string, version: string|null, is_mariadb: bool}
     */
    protected function mysqlClientInfo(): array
    {
        if ($this->mysqlClientInfo !== null) {
            return $this->mysqlClientInfo;
        }

        $binary = trim((string) $this->settings->value(
            'dumper.binaries.mysql',
            $this->config->get('dumper.binaries.mysql', 'mysql')
        ));

        return $this->mysqlClientInfo = $this->binaryClientInfo($binary !== '' ? $binary : 'mysql');
    }

    /**
     * @return array{raw: string, version: string|null, is_mariadb: bool}
     */
    protected function binaryClientInfo(string $binary): array
    {
        try {
            $process = new Process([$binary, '--version']);
            $process->setTimeout(5);
            $process->run();

            if (!$process->isSuccessful()) {
                return [
                    'raw' => '',
                    'version' => null,
                    'is_mariadb' => false,
                ];
            }

            $output = trim($process->getOutput() . ' ' . $process->getErrorOutput());
            $version = null;

            if (preg_match('/(\d+\.\d+\.\d+)/', $output, $matches) === 1) {
                $version = $matches[1];
            }

            return [
                'raw' => $output,
                'version' => $version,
                'is_mariadb' => str_contains(Str::lower($output), 'mariadb'),
            ];
        } catch (Throwable) {
            return [
                'raw' => '',
                'version' => null,
                'is_mariadb' => false,
            ];
        }
    }

    protected function connectionOptionValue(array $options, ?int $pdoKey, array $configKeys): ?string
    {
        if ($pdoKey !== null && array_key_exists($pdoKey, $options)) {
            $value = $options[$pdoKey];
            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        foreach ($configKeys as $key) {
            if (!array_key_exists($key, $this->connectionConfig)) {
                continue;
            }

            $value = $this->connectionConfig[$key];
            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }

    protected function normalizeBooleanConfigValue(mixed $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            $value = trim($value);
        }

        $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        return $filtered;
    }

    protected function isColumnStatisticsFailure(Throwable $exception): bool
    {
        $message = Str::lower($exception->getMessage());

        return str_contains($message, 'column-statistics')
            || str_contains($message, 'unknown variable');
    }

    protected function isSslFailure(Throwable $exception): bool
    {
        $message = Str::lower($exception->getMessage());

        return str_contains($message, 'tls/ssl')
            || str_contains($message, 'ssl error')
            || str_contains($message, 'self-signed certificate')
            || str_contains($message, 'certificate verify failed');
    }

    protected function deleteFileIfExists(string $path): void
    {
        if (is_file($path)) {
            @unlink($path);
        }
    }

    protected function guessTablesFromFilename(string $filename): ?array
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);

        if (!str_contains($name, '__')) {
            return null;
        }

        $parts = explode('__', $name, 2);
        $slug = Str::lower(trim($parts[1] ?? ''));

        if ($slug === '' || $slug === 'full') {
            return ['*'];
        }

        $hasMore = false;
        if (Str::endsWith($slug, '-plus')) {
            $slug = substr($slug, 0, -5);
            $hasMore = true;
        }

        $decoded = $this->decodeTablesFromSlug($slug);

        if ($decoded === []) {
            return null;
        }

        if ($hasMore) {
            $decoded[] = '...';
        }

        return $decoded;
    }

    protected function decodeTablesFromSlug(string $slug): array
    {
        $slug = trim($slug, '-');
        if ($slug === '') {
            return [];
        }

        $remaining = $slug;
        $decoded = [];
        $mappings = $this->tableSlugMappings();

        if ($mappings === []) {
            return [];
        }

        while ($remaining !== '') {
            $matched = false;
            foreach ($mappings as $tableSlug => $tableName) {
                if (!Str::startsWith($remaining, $tableSlug)) {
                    continue;
                }

                $nextChar = substr($remaining, strlen($tableSlug), 1);
                if ($nextChar !== '' && $nextChar !== '-') {
                    continue;
                }

                $decoded[] = $tableName;
                $remaining = ltrim(substr($remaining, strlen($tableSlug)), '-');
                $matched = true;
                break;
            }

            if (!$matched) {
                break;
            }
        }

        return $decoded;
    }

    /**
     * @param array<int, string> $providers
     */
    protected function writeRemoteProvidersMetadata(DumpRecord $record, array $providers): DumpRecord
    {
        $adapter = $this->filesystem->disk($record->disk);
        $metaPath = $this->metaPath($record->path);
        $meta = [];

        if ($adapter->exists($metaPath)) {
            $decoded = json_decode($adapter->get($metaPath), true);
            if (is_array($decoded)) {
                $meta = $decoded;
            }
        }

        $meta['remote_providers'] = array_values(array_unique($providers));

        $adapter->put($metaPath, json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        return $this->buildRecord($record->disk, $record->path) ?? $record;
    }

    /**
     * @return array<int, string>
     */
    protected function normalizeRemoteProviders(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $providers = array_filter(array_map(
            static fn (mixed $provider): string => trim((string) $provider),
            $value
        ));

        return array_values(array_unique($providers));
    }

    /**
     * @return array<string, string>
     */
    protected function tableSlugMappings(): array
    {
        if ($this->tableSlugMap !== null) {
            return $this->tableSlugMap;
        }

        try {
            $tables = $this->inspector->tables();
        } catch (Throwable) {
            $tables = [];
        }

        $map = [];
        foreach ($tables as $table) {
            $slug = Str::slug($table);
            if ($slug === '') {
                continue;
            }

            $map[$slug] = $table;
        }

        uksort($map, fn (string $a, string $b) => strlen($b) <=> strlen($a));

        return $this->tableSlugMap = $map;
    }
}
