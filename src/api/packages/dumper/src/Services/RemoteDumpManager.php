<?php

namespace ParabellumKoval\Dumper\Services;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Filesystem\FilesystemAdapter;
use ParabellumKoval\Dumper\Contracts\RemoteDumpProvider;
use ParabellumKoval\Dumper\Data\DumpRecord;
use ParabellumKoval\Dumper\Jobs\DeleteDumpFromRemoteJob;
use ParabellumKoval\Dumper\Jobs\SyncDumpToRemoteJob;
use RuntimeException;

class RemoteDumpManager
{
    /**
     * @param iterable<int, RemoteDumpProvider> $providers
     */
    public function __construct(
        protected FilesystemFactory $filesystem,
        protected DumperSettings $settings,
        iterable $providers = []
    ) {
        foreach ($providers as $provider) {
            $this->providers[$provider->key()] = $provider;
        }
    }

    /**
     * @var array<string, RemoteDumpProvider>
     */
    protected array $providers = [];

    /**
     * @return array<int, string>
     */
    public function upload(DumpRecord $record): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        [$absoluteDumpPath, $absoluteMetaPath] = $this->localPaths($record);
        $uploadedProviders = [];

        foreach ($this->configuredProviders($this->enabledProviderKeys(), true) as $provider) {
            $provider->upload($record, $absoluteDumpPath, $absoluteMetaPath);
            $uploadedProviders[] = $provider->key();
        }

        return $uploadedProviders;
    }

    public function dispatchUpload(DumpRecord $record): void
    {
        SyncDumpToRemoteJob::dispatch($record->disk, $record->path);
    }

    public function dispatchDelete(DumpRecord $record): void
    {
        DeleteDumpFromRemoteJob::dispatch(
            $record->disk,
            $record->path,
            $record->source,
            $record->caseKey,
            $record->remoteProviders
        );
    }

    public function delete(DumpRecord $record): void
    {
        $providerKeys = $record->remoteProviders;

        if ($providerKeys === [] && !$this->isEnabled()) {
            return;
        }

        if ($providerKeys === []) {
            $providerKeys = $this->enabledProviderKeys();
        }

        foreach ($this->configuredProviders($providerKeys, false) as $provider) {
            $provider->delete($record);
        }
    }

    /**
     * @return array{enabled: bool, providers: array<int, string>, base_directory: string}
     */
    public function status(): array
    {
        $providerLabels = [];

        foreach ($this->enabledProviderKeys() as $key) {
            if (isset($this->providers[$key])) {
                $providerLabels[] = $this->providers[$key]->label();
            }
        }

        return [
            'enabled' => $this->isEnabled(),
            'providers' => $providerLabels,
            'base_directory' => trim((string) $this->settings->value('dumper.remote.base_directory', 'database-backups'), '/'),
        ];
    }

    protected function isEnabled(): bool
    {
        return $this->normalizeBoolean($this->settings->value('dumper.remote.enabled', false)) ?? false;
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    protected function localPaths(DumpRecord $record): array
    {
        $adapter = $this->filesystem->disk($record->disk);

        if (!$adapter instanceof FilesystemAdapter || !method_exists($adapter, 'path')) {
            throw new RuntimeException('Remote upload is only supported for local dump filesystems.');
        }

        $absoluteDumpPath = $adapter->path($record->path);
        $absoluteMetaPath = $adapter->path($record->path . $this->metadataExtension());

        if (!is_file($absoluteMetaPath)) {
            $absoluteMetaPath = null;
        }

        return [$absoluteDumpPath, $absoluteMetaPath];
    }

    /**
     * @param array<int, string> $keys
     * @return array<int, RemoteDumpProvider>
     */
    protected function configuredProviders(array $keys, bool $requireAtLeastOne): array
    {
        $resolved = [];

        foreach ($keys as $key) {
            if (!isset($this->providers[$key])) {
                throw new RuntimeException(sprintf('Remote dump provider [%s] is not registered.', $key));
            }

            $provider = $this->providers[$key];

            if (!$provider->isConfigured()) {
                throw new RuntimeException(sprintf('Remote dump provider [%s] is not fully configured.', $provider->label()));
            }

            $resolved[] = $provider;
        }

        if ($requireAtLeastOne && $resolved === []) {
            throw new RuntimeException('Remote dump sync is enabled, but no providers are selected.');
        }

        return $resolved;
    }

    /**
     * @return array<int, string>
     */
    protected function enabledProviderKeys(): array
    {
        $raw = $this->settings->value('dumper.remote.enabled_providers', []);

        if (is_string($raw)) {
            $raw = array_map('trim', explode(',', $raw));
        }

        if (!is_array($raw)) {
            return [];
        }

        $keys = array_filter(array_map(
            static fn (mixed $value): string => trim((string) $value),
            $raw
        ));

        return array_values(array_unique($keys));
    }

    protected function normalizeBoolean(mixed $value): ?bool
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

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    protected function metadataExtension(): string
    {
        return (string) $this->settings->value('dumper.metadata_extension', '.meta.json');
    }
}
