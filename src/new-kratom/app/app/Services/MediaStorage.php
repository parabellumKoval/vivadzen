<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Единый сервис загрузки картинок. Поддерживает два бэкенда:
 *
 *  - local: пишет на Laravel-диск (по умолчанию `public`), URL генерируется через asset()/'/storage'.
 *  - bunny: льёт оригинал в Bunny Storage Zone, отдаёт URL через настроенный pull-zone CDN.
 *
 * Возвращает [disk, path, url] — этого достаточно, чтобы запомнить где файл лежит
 * и куда его удалять, а так же отдать готовый URL фронту/CacheWarmer.
 */
class MediaStorage
{
    /**
     * Сохранить uploaded-файл.
     *
     * @return array{disk:string,path:string,url:string,filename:string,mime:string,size:int}
     */
    public function store(UploadedFile $file, string $folder = 'uploads'): array
    {
        $driver = $this->driver();
        $folder = trim($folder, '/');
        $filename = $this->generateFilename($file);
        $relativePath = $folder.'/'.$filename;

        if ($driver === 'bunny' && $this->bunnyConfigured()) {
            $this->putBunny($file->getRealPath(), $relativePath, $file->getMimeType() ?: 'application/octet-stream');

            return [
                'disk' => 'bunny',
                'path' => $relativePath,
                'url' => $this->bunnyUrl($relativePath),
                'filename' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize() ?: 0,
            ];
        }

        $disk = config('media.local.disk', 'public');
        $path = $file->storeAs($folder, $filename, $disk);

        return [
            'disk' => $disk,
            'path' => $path,
            'url' => $this->localUrl($path),
            'filename' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize() ?: 0,
        ];
    }

    /**
     * Удалить файл (если ещё существует). Никогда не бросает: лог + false.
     */
    public function delete(?string $disk, ?string $path): bool
    {
        if (! $disk || ! $path) {
            return false;
        }

        try {
            if ($disk === 'bunny') {
                if (! $this->bunnyConfigured()) {
                    return false;
                }
                $this->deleteBunny($path);

                return true;
            }

            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->delete($path);
            }
        } catch (\Throwable $e) {
            Log::warning('MediaStorage::delete failed', [
                'disk' => $disk, 'path' => $path, 'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Построить URL для уже сохранённого файла (используется на чтении из БД).
     *
     * Если path уже содержит схему/host или начинается с '/' (абсолютный
     * корневой путь, как у seed-данных вида /assets/products/...), отдаём
     * как есть — таким файлам префикс /storage не нужен.
     */
    public function url(string $disk, string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://', '//', '/'])) {
            return $path;
        }

        if ($disk === 'bunny') {
            return $this->bunnyUrl($path);
        }

        return $this->localUrl($path);
    }

    public function driver(): string
    {
        return config('media.driver', 'local');
    }

    // ── internal ───────────────────────────────────────────────────────

    private function generateFilename(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';

        return $name.'-'.Str::lower(Str::random(8)).'.'.$ext;
    }

    private function localUrl(string $path): string
    {
        $prefix = rtrim(config('media.local.url_prefix', '/storage'), '/');

        return $prefix.'/'.ltrim($path, '/');
    }

    private function bunnyConfigured(): bool
    {
        return (bool) config('media.bunny.zone')
            && (bool) config('media.bunny.key')
            && (bool) config('media.bunny.pull_zone');
    }

    private function bunnyUrl(string $path): string
    {
        return rtrim(config('media.bunny.pull_zone'), '/').'/'.ltrim($path, '/');
    }

    private function putBunny(string $localPath, string $remotePath, string $mime): void
    {
        $zone = config('media.bunny.zone');
        $host = rtrim(config('media.bunny.host', 'storage.bunnycdn.com'), '/');
        $url = "https://{$host}/{$zone}/".ltrim($remotePath, '/');

        $response = Http::withHeaders([
            'AccessKey' => config('media.bunny.key'),
            'Content-Type' => $mime,
        ])
            ->withBody(file_get_contents($localPath), $mime)
            ->put($url);

        if (! $response->successful()) {
            throw new \RuntimeException('Bunny upload failed: '.$response->status().' '.$response->body());
        }
    }

    private function deleteBunny(string $remotePath): void
    {
        $zone = config('media.bunny.zone');
        $host = rtrim(config('media.bunny.host', 'storage.bunnycdn.com'), '/');
        $url = "https://{$host}/{$zone}/".ltrim($remotePath, '/');

        Http::withHeaders(['AccessKey' => config('media.bunny.key')])->delete($url);
    }
}
