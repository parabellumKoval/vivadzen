<?php

namespace ParabellumKoval\BackpackImages\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ParabellumKoval\BackpackImages\Contracts\ImageStorageProvider;
use ParabellumKoval\BackpackImages\DTO\StoredImage;
use ParabellumKoval\BackpackImages\Exceptions\ImageUploadException;
use ParabellumKoval\BackpackImages\Support\FileNameGenerator;
use ParabellumKoval\BackpackImages\Support\ImageProviderRegistry;
use ParabellumKoval\BackpackImages\Support\ImageUploadOptions;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use finfo;

class ImageUploader
{
    public function __construct(
        private readonly ImageProviderRegistry $providers,
        private readonly FileNameGenerator $fileNameGenerator,
        private readonly array $config
    ) {
    }

    public function uploadOne(string $url, string $folder = '', ?string $providerName = null): array
    {
        $options = $this->resolveOptions(null, $folder, $providerName);

        return $this->upload($url, $options)->toArray();
    }

    public function uploadMany(array $urls, string $folder = '', ?string $providerName = null): array
    {
        $options = $this->resolveOptions(null, $folder, $providerName);
        $results = [];

        foreach ($urls as $url) {
            try {
                $results[] = $this->upload($url, $options)->toArray();
            } catch (ImageUploadException $exception) {
                Log::channel($options->logChannel ?? $this->defaultLogChannel())
                    ->error(sprintf('Error uploading %s: %s', $url, $exception->getMessage()));
            }
        }

        return $results;
    }

    public function delete(string $path, ?string $providerName = null): bool
    {
        $options = $this->resolveOptions(null, null, $providerName);
        $provider = $this->resolveProvider($options->provider);
        $logChannel = $options->logChannel ?? $this->defaultLogChannel();

        try {
            $deleted = $provider->delete($path);

            if ($deleted) {
                Log::channel($logChannel)->info(sprintf('Deleted file %s from provider %s', $path, $options->provider));
            } else {
                Log::channel($logChannel)->warning(sprintf('File %s not found or could not be deleted on provider %s', $path, $options->provider));
            }

            return $deleted;
        } catch (Exception $exception) {
            Log::channel($logChannel)->error(sprintf('Error deleting %s on %s: %s', $path, $options->provider, $exception->getMessage()));

            return false;
        }
    }

    public function uploadFromBase64(string $dataUri, ?ImageUploadOptions $options = null): StoredImage
    {
        $options = $this->resolveOptions($options);
        $provider = $this->resolveProvider($options->provider);
        $logChannel = $options->logChannel ?? $this->defaultLogChannel();

        Log::channel($logChannel)->info(sprintf('Starting image upload from base64 using provider: %s', $options->provider));

        $decoded = $this->decodeBase64Image($dataUri, $logChannel);
        $targetPath = $this->fileNameGenerator->generate(
            $decoded['originalName'],
            $decoded['extension'],
            $provider,
            $options->resolvedFolder($this->config['default_folder'] ?? ''),
            $options->preserveOriginalName ?? false,
            $options->generateUniqueName ?? true
        );

        try {
            $provider->upload($decoded['content'], $targetPath);
        } catch (Exception $exception) {
            Log::channel($logChannel)->error(sprintf('Upload failed for %s via %s: %s', $targetPath, $options->provider, $exception->getMessage()));

            throw new ImageUploadException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        Log::channel($logChannel)->info(sprintf('Image uploaded to %s via %s', $targetPath, $options->provider));

        return new StoredImage(
            $provider->getUrl($targetPath),
            $targetPath,
            basename($targetPath),
            $decoded['extension']
        );
    }

    public function uploadFromFile(SymfonyFile $file, ?ImageUploadOptions $options = null): StoredImage
    {
        $options = $this->resolveOptions($options);
        $provider = $this->resolveProvider($options->provider);
        $logChannel = $options->logChannel ?? $this->defaultLogChannel();

        $fileName = $file->getFilename();
        Log::channel($logChannel)->info(sprintf('Starting image upload from file %s using provider: %s', $fileName, $options->provider));

        $originalName = $file instanceof UploadedFile
            ? (string) $file->getClientOriginalName()
            : (string) $fileName;

        $extension = $file instanceof UploadedFile
            ? (string) $file->getClientOriginalExtension()
            : ((string) $file->guessExtension());

        $realPath = $file->getRealPath();
        $content = $realPath ? @file_get_contents($realPath) : false;

        if ($content === false) {
            Log::channel($logChannel)->error(sprintf('Failed to read file contents for %s', $fileName));

            throw new ImageUploadException('Unable to read uploaded file contents.');
        }

        if ($extension === '' || $extension === null) {
            $extension = $this->detectExtension($content, (string) $file->getMimeType());
        }

        $targetPath = $this->fileNameGenerator->generate(
            $originalName,
            $extension,
            $provider,
            $options->resolvedFolder($this->config['default_folder'] ?? ''),
            $options->preserveOriginalName ?? false,
            $options->generateUniqueName ?? true
        );

        try {
            $provider->upload($content, $targetPath);
        } catch (Exception $exception) {
            Log::channel($logChannel)->error(sprintf('Upload failed for %s via %s: %s', $targetPath, $options->provider, $exception->getMessage()));

            throw new ImageUploadException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        Log::channel($logChannel)->info(sprintf('Image uploaded to %s via %s', $targetPath, $options->provider));

        return new StoredImage(
            $provider->getUrl($targetPath),
            $targetPath,
            basename($targetPath),
            $extension
        );
    }

    public function upload(string $url, ?ImageUploadOptions $options = null): StoredImage
    {
        $options = $this->resolveOptions($options);
        $provider = $this->resolveProvider($options->provider);
        $logChannel = $options->logChannel ?? $this->defaultLogChannel();

        Log::channel($logChannel)->info(sprintf('Starting image upload from URL: %s using provider: %s', $url, $options->provider));

        $download = $this->downloadImage($url, $logChannel);
        $targetPath = $this->fileNameGenerator->generate(
            $download['originalName'],
            $download['extension'],
            $provider,
            $options->resolvedFolder($this->config['default_folder'] ?? ''),
            $options->preserveOriginalName ?? false,
            $options->generateUniqueName ?? true
        );

        try {
            $provider->upload($download['content'], $targetPath);
        } catch (Exception $exception) {
            Log::channel($logChannel)->error(sprintf('Upload failed for %s via %s: %s', $targetPath, $options->provider, $exception->getMessage()));

            throw new ImageUploadException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        Log::channel($logChannel)->info(sprintf('Image uploaded to %s via %s', $targetPath, $options->provider));

        return new StoredImage(
            $provider->getUrl($targetPath),
            $targetPath,
            basename($targetPath),
            $download['extension']
        );
    }

    public function getProvider(string $name): ImageStorageProvider
    {
        return $this->resolveProvider($name);
    }

    public function getDefaultOptions(): ImageUploadOptions
    {
        return ImageUploadOptions::fromConfig($this->config);
    }

    protected function resolveOptions(?ImageUploadOptions $options, ?string $folder = null, ?string $providerName = null): ImageUploadOptions
    {
        $resolved = $options ? $options->merge($this->getDefaultOptions()) : $this->getDefaultOptions();

        if ($folder !== null) {
            $resolved->folder = $folder;
        }

        if ($providerName !== null) {
            $resolved->provider = $providerName;
        }

        if (!$resolved->provider) {
            $resolved->provider = $this->config['default_provider'] ?? 'local';
        }

        return $resolved;
    }

    protected function resolveProvider(?string $name): ImageStorageProvider
    {
        $providerName = $name ?: ($this->config['default_provider'] ?? 'local');

        return $this->providers->resolve($providerName);
    }

    protected function downloadImage(string $url, string $logChannel): array
    {
        try {
            $response = Http::get($url);

            if (!$response->successful()) {
                $status = $response->status();
                Log::channel($logChannel)->error(sprintf('Failed to download image from %s, HTTP status %s', $url, $status));

                throw new ImageUploadException(sprintf('Failed to download image: HTTP %s', $status));
            }

            $content = $response->body();
        } catch (Exception $exception) {
            Log::channel($logChannel)->error(sprintf('Error downloading image from %s: %s', $url, $exception->getMessage()));

            throw new ImageUploadException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        [$originalName, $extension] = $this->resolveFileName($url);

        if (!$extension) {
            $extension = $this->detectExtension($content, (string) $response->header('Content-Type'));
        }

        return [
            'content' => $content,
            'originalName' => $originalName,
            'extension' => $extension,
        ];
    }

    protected function resolveFileName(string $url): array
    {
        $originalName = basename(parse_url($url, PHP_URL_PATH) ?? '') ?: '';
        $originalName = urldecode($originalName);
        $extension = '';

        if (str_contains($originalName, '.')) {
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        }

        return [$originalName, $extension];
    }

    protected function detectExtension(string $content, string $contentType = ''): string
    {
        $extension = '';

        if ($contentType !== '') {
            $extension = $this->extensionFromMime($contentType);
        }

        if ($extension === '') {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $extension = $this->extensionFromMime((string) $finfo->buffer($content));
        }

        return $extension ?: 'jpg';
    }

    protected function decodeBase64Image(string $dataUri, string $logChannel): array
    {
        if (!preg_match('#^data:(?P<mime>[^;]+);base64,(?P<data>.+)$#si', $dataUri, $matches)) {
            Log::channel($logChannel)->error('Invalid base64 image data provided.');

            throw new ImageUploadException('Invalid base64 image data.');
        }

        $encoded = preg_replace('/\s+/', '', $matches['data']);
        $content = base64_decode($encoded, true);

        if ($content === false) {
            Log::channel($logChannel)->error('Failed to decode base64 image content.');

            throw new ImageUploadException('Failed to decode base64 image content.');
        }

        $mime = (string) ($matches['mime'] ?? '');
        $extension = $this->detectExtension($content, $mime);

        return [
            'content' => $content,
            'extension' => $extension,
            'originalName' => '',
        ];
    }

    protected function extensionFromMime(string $mime): string
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => '',
        };
    }

    protected function defaultLogChannel(): string
    {
        return $this->config['logging_channel'] ?? 'stack';
    }
}
