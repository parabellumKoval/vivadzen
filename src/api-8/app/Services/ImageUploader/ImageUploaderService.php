<?php

namespace App\Services\ImageUploader;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;
use finfo;

class ImageUploaderService
{
    protected string $defaultProvider;
    protected string $defaultFolder;
    protected bool $preserveOriginal;
    protected bool $generateUnique;
    protected string $logChannel;

    protected LocalStorageProvider $localProvider;
    protected BunnyCDNStorageProvider $bunnyProvider;

    public function __construct(LocalStorageProvider $localProvider, BunnyCDNStorageProvider $bunnyProvider)
    {
        // Чтение настроек из конфига
        $this->defaultProvider = config('imageupload.default_provider', 'local');
        $this->defaultFolder   = rtrim(config('imageupload.default_folder', ''), '/');
        $this->preserveOriginal = config('imageupload.preserve_original_name', true);
        $this->generateUnique   = config('imageupload.generate_unique_name', false);
        $this->logChannel       = config('imageupload.logging_channel', 'stack');

        // Провайдеры, внедренные через сервис-провайдер
        $this->localProvider = $localProvider;
        $this->bunnyProvider = $bunnyProvider;
    }

    /** Возвращает провайдер по имени */
    protected function getProvider(string $providerName): StorageProviderInterface
    {
        $name = strtolower($providerName);
        return $name === 'bunny' ? $this->bunnyProvider : $this->localProvider;
    }

    /** Вспомогательный метод для извлечения имени файла и расширения из URL */
    protected function resolveFileName(string $url): array
    {
        // Получаем имя файла из пути URL
        $originalName = basename(parse_url($url, PHP_URL_PATH) ?? '');
        $originalName = urldecode($originalName);
        $extension = '';
        if (str_contains($originalName, '.')) {
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        }
        // Вернем текущее имя (оно может быть пустым или без расширения)
        return [$originalName, $extension];
    }

    /**
     * Загрузка изображения по одному URL.
     * @param string $url – внешний URL изображения
     * @param string $folder – (опционально) подпапка для сохранения
     * @param string|null $providerName – (опционально) явный выбор провайдера ('local' или 'bunny')
     * @return string – публичный URL сохраненного изображения 
     * @throws Exception в случае ошибок загрузки
     */
    public function uploadOne(string $url, string $folder = '', ?string $providerName = null): array
    {
        $providerName = $providerName ?: $this->defaultProvider;
        $provider = $this->getProvider($providerName);

        // Определяем целевую папку: объединяем базовую папку из конфига и указанную подпапку
        $baseFolder = $this->defaultFolder;
        if ($folder !== '') {
            $folder = trim($folder, '/');
            $baseFolder = $baseFolder ? ($baseFolder . '/' . $folder) : $folder;
        }

        // Логируем начало процесса
        Log::channel($this->logChannel)->info("Starting image upload from URL: $url using provider: $providerName");

        // Шаг 1: Скачиваем содержимое изображения по внешнему URL
        try {
            $response = Http::get($url);  // используем Laravel HTTP-клиент:contentReference[oaicite:8]{index=8}
            if (!$response->successful()) {
                $status = $response->status();
                Log::channel($this->logChannel)->error("Failed to download image from $url, HTTP status $status");
                throw new Exception("Failed to download image: HTTP $status");
            }
            $content = $response->body();
        } catch (Exception $e) {
            Log::channel($this->logChannel)->error("Error downloading image from $url: {$e->getMessage()}");
            throw $e;
        }

        // Шаг 2: Определяем имя файла и расширение
        [$originalName, $extension] = $this->resolveFileName($url);
        if (!$extension) {
            // Если расширение не определено из URL, пытаемся определить по MIME-типу содержимого
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($content);
            if ($mimeType) {
                if ($mimeType === 'image/jpeg')    $extension = 'jpg';
                elseif ($mimeType === 'image/png') $extension = 'png';
                elseif ($mimeType === 'image/gif') $extension = 'gif';
                elseif ($mimeType === 'image/webp') $extension = 'webp';
            }
            if (!$extension) {
                $extension = 'jpg'; // по умолчанию используем jpg, если не удалось определить
            }
        }

        // Формируем конечное имя файла с учетом настроек
        $targetName = '';
        if ($this->preserveOriginal) {
            // Сохраняем оригинальное имя (без расширения) или генерация базового имени, если оригинальное пустое
            $baseName = pathinfo($originalName, PATHINFO_FILENAME);
            if (!$baseName) {
                $baseName = 'image_' . date('Ymd_His');
            }
            // Начальное имя файла с исходным расширением
            $targetName = $baseName . '.' . $extension;
            if ($this->generateUnique) {
                // Если настроено генерировать уникальные имена *вместе* с оригинальными – добавляем случайный суффикс
                $uniqueId = Str::random(8);
                $targetName = $baseName . '_' . $uniqueId . '.' . $extension;
            } else {
                // Проверяем на коллизии и добавляем (2), (3), ... если файл с таким именем уже существует
                $counter = 2;
                $nameWithoutExt = $baseName;
                // Проверяем существование файла по текущему имени
                while ($provider->exists(($baseFolder ? $baseFolder . '/' : '') . $targetName)) {
                    $targetName = $nameWithoutExt . "_($counter)." . $extension;
                    $counter++;
                }
            }
        } else {
            // Оригинальное имя не сохраняем – генерируем уникальное имя файла (например, UUID)
            $uniqueId = Str::uuid()->toString();
            $targetName = $uniqueId . '.' . $extension;
            if (!$this->generateUnique) {
                // Если почему-то оба флага (preserve и generate) false, всё равно избегаем коллизий суффиксами
                $counter = 2;
                $nameWithoutExt = $uniqueId;
                while ($provider->exists(($baseFolder ? $baseFolder . '/' : '') . $targetName)) {
                    $targetName = $nameWithoutExt . "_($counter)." . $extension;
                    $counter++;
                }
            }
        }

        // Шаг 3: Формируем полный путь для сохранения (с учетом папок)
        $finalPath = $targetName;
        if ($baseFolder) {
            $finalPath = $baseFolder . '/' . $targetName;
        }

        // Шаг 4: Загружаем содержимое изображения через выбранный провайдер
        try {
            $provider->upload($content, $finalPath);
        } catch (Exception $e) {
            Log::channel($this->logChannel)->error("Upload failed for $finalPath via $providerName: {$e->getMessage()}");
            throw $e;
        }

        // Логируем успех
        Log::channel($this->logChannel)->info("Image uploaded to $finalPath via $providerName");
        // Шаг 5: Возвращаем публичный URL сохраненного файла
        return [
            'url' => $provider->getUrl($finalPath),
            'path' => $finalPath,
            'filename' => $targetName,
            'extension' => $extension
        ];
    }

    /**
     * Загрузка нескольких изображений по массиву URL.
     * Возвращает массив результатов (публичных URL) для успешно загруженных файлов.
     * В случае ошибок для отдельных файлов – ошибки логируются, обработка продолжается.
     */
    public function uploadMany(array $urls, string $folder = '', ?string $providerName = null): array
    {
        $results = [];
        foreach ($urls as $url) {
            try {
                $results[] = $this->uploadOne($url, $folder, $providerName);
            } catch (Exception $e) {
                // Логируем ошибку и продолжаем со следующими URL
                Log::channel($this->logChannel)->error("Error uploading $url: {$e->getMessage()}");
            }
        }
        return $results;
    }

    /**
     * Удаление файла из хранилища.
     * @param string $path – относительный путь к файлу в хранилище (с учетом default_folder, если был установлен)
     * @param string|null $providerName – (опционально) провайдер ('local' или 'bunny'), если нужно явно указать
     * @return bool – true, если файл успешно удалён или не существовал; false, если произошла ошибка
     */
    public function delete(string $path, ?string $providerName = null): bool
    {
        $providerName = $providerName ?: $this->defaultProvider;
        $provider = $this->getProvider($providerName);
        try {
            $deleted = $provider->delete($path);
            if ($deleted) {
                Log::channel($this->logChannel)->info("Deleted file $path from provider $providerName");
            } else {
                Log::channel($this->logChannel)->warning("File $path not found or could not be deleted on provider $providerName");
            }
            return $deleted;
        } catch (Exception $e) {
            Log::channel($this->logChannel)->error("Error deleting $path on $providerName: {$e->getMessage()}");
            return false;
        }
    }
}
