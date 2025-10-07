<?php

namespace ParabellumKoval\BackpackImages\Providers;

use Bunny\Storage\Client;
use Exception;
use ParabellumKoval\BackpackImages\Contracts\ConfigurableImageProvider;
use ParabellumKoval\BackpackImages\Contracts\ImageStorageProvider;

class BunnyCdnImageProvider implements ImageStorageProvider, ConfigurableImageProvider
{
    protected Client $client;

    protected string $rootFolder;

    protected string $pullZoneUrl;

    public function __construct(string $storageZone, string $apiKey, string $region, string $rootFolder, string $pullZoneUrl)
    {
        $regionCode = $region ? strtolower($region) : '';

        $this->rootFolder = trim($rootFolder, '/');
        $this->pullZoneUrl = rtrim($pullZoneUrl, '/');
        $this->client = new Client($apiKey, $storageZone, $regionCode);
    }

    public static function fromConfig(array $config): static
    {
        return new static(
            $config['storage_zone'] ?? '',
            $config['api_key'] ?? '',
            $config['region'] ?? '',
            $config['root_folder'] ?? '',
            $config['pull_zone_url'] ?? ''
        );
    }

    protected function applyRootFolder(string $path): string
    {
        $path = ltrim($path, '/');

        return $this->rootFolder !== ''
            ? $this->rootFolder . '/' . $path
            : $path;
    }

    public function upload(string $content, string $path): string
    {
        $remotePath = $this->applyRootFolder($path);
        $this->client->putContents($remotePath, $content);

        return $path;
    }

    public function exists(string $path): bool
    {
        $remotePath = $this->applyRootFolder($path);

        try {
            return $this->client->info($remotePath) !== null;
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete(string $path): bool
    {
        $remotePath = $this->applyRootFolder($path);

        try {
            $this->client->delete($remotePath);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getUrl(string $path): string
    {
        $remotePath = $this->applyRootFolder($path);

        if ($this->pullZoneUrl === '') {
            return $remotePath;
        }

        return $this->pullZoneUrl . '/' . ltrim($remotePath, '/');
    }
}
