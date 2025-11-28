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
use PDO;
use RuntimeException;
use Spatie\DbDumper\Databases\MySql;
use Symfony\Component\Process\Process;
use Throwable;

class DumpManager
{
    protected string $connectionName;

    /**
     * @var array<string, mixed>
     */
    protected array $connectionConfig;

    protected string $metaExtension;

    protected string $mysqldumpBinary = 'mysqldump';

    protected ?bool $mysqldumpSupportsColumnStatistics = null;

    /**
     * @var array<string, string>|null
     */
    protected ?array $tableSlugMap = null;

    public function __construct(
        protected FilesystemFactory $filesystem,
        protected DatabaseManager $database,
        protected Repository $config,
        protected TableInspector $inspector
    ) {
        $this->connectionName = $this->inspector->connectionName();
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
        $cases = (array) $this->config->get('dumper.auto.cases', []);
        $baseDirectory = $this->normalizeDirectory((string) $this->config->get('dumper.auto.base_directory', 'dumps/auto'));
        $baseDisk = (string) $this->config->get('dumper.auto.disk', $this->manualConfig()['disk']);
        $prefix = (string) $this->config->get('dumper.auto.filename_prefix', 'auto');

        $normalized = [];

        foreach ($cases as $key => $definition) {
            $definition = (array) $definition;
            $directory = $this->normalizeDirectory($definition['directory'] ?? $definition['path'] ?? $key);
            $fullDirectory = $directory;
            if ($baseDirectory !== '') {
                $fullDirectory = $baseDirectory . '/' . ltrim($directory, '/');
            }

            $normalized[$key] = [
                'key' => (string) $key,
                'label' => $definition['label'] ?? Str::headline((string) $key),
                'tables' => $definition['tables'] ?? '*',
                'cron' => $definition['cron'] ?? null,
                'description' => $definition['description'] ?? null,
                'disk' => $definition['disk'] ?? $baseDisk,
                'directory' => $fullDirectory,
                'retention' => $definition['retention'] ?? [],
                'filename_prefix' => $definition['filename_prefix'] ?? $prefix,
            ];
        }

        return $normalized;
    }

    public function createManualDump(?array $tables, ?string $label = null): DumpRecord
    {
        $manual = $this->manualConfig();

        return $this->createDump(
            $manual['disk'],
            $manual['directory'],
            $manual['filename_prefix'],
            'manual',
            null,
            $this->normalizeTablesInput($tables),
            $label
        );
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

        $process = new Process($this->buildMysqlCommand());
        $process->setTimeout(null);
        $stream = fopen($filePath, 'rb');

        if ($stream === false) {
            throw new RuntimeException('Unable to read dump file for restore.');
        }

        try {
            $process->setInput($stream);
            $process->mustRun();
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    public function delete(DumpRecord $record): void
    {
        $adapter = $this->filesystem->disk($record->disk);

        $adapter->delete($record->path);
        $adapter->delete($this->metaPath($record->path));
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

        $this->buildDumper($tables)->dumpToFile($this->absolutePath($adapter, $relativePath));

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

        return $this->buildRecord($disk, $relativePath);
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
            (int) $adapter->size($path)
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
        $manual = (array) $this->config->get('dumper.manual', []);

        return [
            'disk' => $manual['disk'] ?? 'local',
            'directory' => $manual['directory'] ?? 'dumps/manual',
            'filename_prefix' => $manual['filename_prefix'] ?? 'manual',
        ];
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

    protected function buildDumper(?array $tables): MySql
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

        if ($this->shouldDisableColumnStatistics()) {
            $dumper->doNotUseColumnStatistics();
        }

        if ($tables !== null) {
            $dumper->includeTables($tables);
        }

        $dumper->useSingleTransaction();

        return $dumper;
    }

    protected function buildMysqlCommand(): array
    {
        $config = $this->connectionConfig;
        $binary = trim((string) $this->config->get('dumper.binaries.mysql', 'mysql'));

        $command = [$binary];

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

        $command[] = $config['database'] ?? '';

        return $command;
    }

    protected function configureMysqldumpBinary(MySql $dumper): void
    {
        $binaryPath = trim((string) $this->config->get('dumper.binaries.mysqldump', ''));
        $binary = 'mysqldump';

        if ($binaryPath !== '') {
            $binaryPath = rtrim($binaryPath, '/');
            $dumper->setDumpBinaryPath($binaryPath . '/');
            $binary = $binaryPath . '/mysqldump';
        }

        if ($this->mysqldumpBinary !== $binary) {
            $this->mysqldumpBinary = $binary;
            $this->mysqldumpSupportsColumnStatistics = null;
        }
    }

    protected function shouldDisableColumnStatistics(): bool
    {
        $configured = $this->normalizeBooleanConfigValue($this->config->get('dumper.options.disable_column_statistics'));

        if ($configured !== null) {
            return $configured;
        }

        return $this->mysqldumpSupportsColumnStatisticsOption();
    }

    protected function mysqldumpSupportsColumnStatisticsOption(): bool
    {
        if ($this->mysqldumpSupportsColumnStatistics !== null) {
            return $this->mysqldumpSupportsColumnStatistics;
        }

        try {
            $process = new Process([$this->mysqldumpBinary, '--version']);
            $process->setTimeout(5);
            $process->run();

            if (!$process->isSuccessful()) {
                return $this->mysqldumpSupportsColumnStatistics = false;
            }

            $output = trim($process->getOutput() . ' ' . $process->getErrorOutput());
            if ($output === '') {
                return $this->mysqldumpSupportsColumnStatistics = false;
            }

            if (str_contains(Str::lower($output), 'mariadb')) {
                return $this->mysqldumpSupportsColumnStatistics = false;
            }

            if (preg_match('/(\d+\.\d+\.\d+)/', $output, $matches) === 1) {
                return $this->mysqldumpSupportsColumnStatistics = version_compare($matches[1], '8.0.0', '>=');
            }
        } catch (Throwable) {
            return $this->mysqldumpSupportsColumnStatistics = false;
        }

        return $this->mysqldumpSupportsColumnStatistics = false;
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
