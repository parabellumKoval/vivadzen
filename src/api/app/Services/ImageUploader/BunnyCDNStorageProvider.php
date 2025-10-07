<?php

namespace App\Services\ImageUploader;

use Bunny\Storage\Client;
use Exception;

class BunnyCDNStorageProvider implements StorageProviderInterface
{
    protected Client $client;
    protected string $rootFolder;
    protected string $pullZoneUrl;

    public function __construct(string $storageZone, string $apiKey, string $region, string $rootFolder, string $pullZoneUrl)
    {
        // Настройка региона: для региона Falkenstein (DE) используется пустая строка
        if (!$region) {
            $regionCode = '';
        } elseif (strtolower($region) === 'de') {
            $regionCode = $region;
        } else {
            $regionCode = $region;
        }

        $this->rootFolder = trim($rootFolder, '/');
        $this->pullZoneUrl = rtrim($pullZoneUrl, '/');
        // Инициализация BunnyCDN API клиента с заданным Storage Zone, API-ключом и регионом
        $this->client = new Client($apiKey, $storageZone, $regionCode);  // BunnyCDN PHP API клиент:contentReference[oaicite:4]{index=4}
    }

    /** Вспомогательный метод для учета корневой папки (если задана) */
    protected function applyRootFolder(string $path): string
    {
        $path = ltrim($path, '/');
        return $this->rootFolder !== '' ? ($this->rootFolder . '/' . $path) : $path;
    }

    public function upload(string $content, string $path): string
    {
        $remotePath = $this->applyRootFolder($path);
        // Загружаем содержимое файла в BunnyCDN Storage. Метод putContents отправляет данные файла по API:contentReference[oaicite:5]{index=5} 
        $this->client->putContents($remotePath, $content);
        return $path;
    }

    public function exists(string $path): bool
    {
        $remotePath = $this->applyRootFolder($path);
        try {
            // Проверяем наличие файла с помощью BunnyCDN API
            $info = $this->client->info($remotePath);
            return $info !== null;
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete(string $path): bool
    {
        $remotePath = $this->applyRootFolder($path);
        try {
            $this->client->delete($remotePath);  // Удаляем файл из BunnyCDN Storage:contentReference[oaicite:6]{index=6}
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getUrl(string $path): string
    {
        $remotePath = $this->applyRootFolder($path);
        // Генерируем публичный URL на основе Pull Zone URL и пути в storage
        return $this->pullZoneUrl . '/' . ltrim($remotePath, '/');
    }
}
