<?php

namespace ParabellumKoval\Dumper\RemoteProviders;

use Bunny\Storage\Client;
use Bunny\Storage\FileNotFoundException;
use Bunny\Storage\Region;
use ParabellumKoval\Dumper\Contracts\RemoteDumpProvider;
use ParabellumKoval\Dumper\Data\DumpRecord;
use ParabellumKoval\Dumper\Services\DumperSettings;

class BunnyRemoteDumpProvider implements RemoteDumpProvider
{
    public function __construct(
        protected DumperSettings $settings
    ) {
    }

    public function key(): string
    {
        return 'bunny';
    }

    public function label(): string
    {
        return 'BunnyCDN';
    }

    public function isConfigured(): bool
    {
        return $this->storageZone() !== '' && $this->apiKey() !== '';
    }

    public function upload(DumpRecord $record, string $absoluteDumpPath, ?string $absoluteMetaPath = null): void
    {
        $client = $this->client();
        $client->upload($absoluteDumpPath, $this->remotePath($record->path));

        if ($absoluteMetaPath && is_file($absoluteMetaPath)) {
            $client->upload($absoluteMetaPath, $this->remotePath($record->path . $this->metadataExtension()));
        }
    }

    public function delete(DumpRecord $record): void
    {
        $client = $this->client();

        try {
            $client->delete($this->remotePath($record->path));
        } catch (FileNotFoundException) {
        }

        try {
            $client->delete($this->remotePath($record->path . $this->metadataExtension()));
        } catch (FileNotFoundException) {
        }
    }

    protected function client(): Client
    {
        return new Client(
            $this->apiKey(),
            $this->storageZone(),
            $this->region()
        );
    }

    protected function remotePath(string $path): string
    {
        $segments = [];
        $globalBaseDirectory = trim((string) $this->settings->value('dumper.remote.base_directory', 'database-backups'), '/');
        $providerBaseDirectory = trim((string) $this->settings->value('dumper.remote.providers.bunny.base_directory', ''), '/');
        $relativePath = ltrim($path, '/');

        if ($globalBaseDirectory !== '') {
            $segments[] = $globalBaseDirectory;
        }

        if ($providerBaseDirectory !== '') {
            $segments[] = $providerBaseDirectory;
        }

        if ($relativePath !== '') {
            $segments[] = $relativePath;
        }

        return implode('/', $segments);
    }

    protected function apiKey(): string
    {
        return trim((string) $this->settings->value('dumper.remote.providers.bunny.api_key', ''));
    }

    protected function storageZone(): string
    {
        return trim((string) $this->settings->value('dumper.remote.providers.bunny.storage_zone', ''));
    }

    protected function region(): string
    {
        $region = trim((string) $this->settings->value('dumper.remote.providers.bunny.region', Region::FALKENSTEIN));

        return isset(Region::LIST[$region]) ? $region : Region::FALKENSTEIN;
    }

    protected function metadataExtension(): string
    {
        return (string) $this->settings->value('dumper.metadata_extension', '.meta.json');
    }
}
