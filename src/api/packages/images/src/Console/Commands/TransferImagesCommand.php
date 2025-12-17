<?php

namespace ParabellumKoval\BackpackImages\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ParabellumKoval\BackpackImages\Contracts\ImageStorageProvider;
use ParabellumKoval\BackpackImages\DTO\StoredImage;
use ParabellumKoval\BackpackImages\Services\ImageUploader;
use ParabellumKoval\BackpackImages\Support\ImageUploadOptions;
use Symfony\Component\HttpFoundation\File\File;
use Throwable;

class TransferImagesCommand extends Command
{
    protected $signature = 'backpack-images:transfer
        {model : Fully qualified model class that uses the HasImages trait}
        {--attribute=images : Model attribute that stores images}
        {--source= : Source provider name (defaults to backpack-images.default_provider)}
        {--target= : Target provider name (defaults to backpack-images.default_provider)}
        {--folder= : Destination folder on the target provider}
        {--chunk=100 : Number of records processed per chunk}
        {--dry-run : Simulate the transfer without persisting changes}
        {--keep-names : Preserve original file names when uploading to the target provider}
        {--skip-file= : Path to file with already processed model IDs (format: one entry per line, e.g. "Model #42")}
        {--skip-before-id= : Skip all records with ID less than or equal to this value}';

    protected $description = 'Transfer stored image references from one provider to another and update the model attribute paths.';

    /** @var array<string, string> */
    protected array $transferredCache = [];

    /** @var array<int> */
    protected array $skippedIds = [];

    /** @var int|null */
    protected ?int $skipBeforeId = null;

    public function handle(ImageUploader $uploader): int
    {
        $modelClass = ltrim((string) $this->argument('model'), '\\');
        $attribute = (string) ($this->option('attribute') ?: 'images');
        $chunkSize = $this->resolveChunkSize();
        $dryRun = (bool) $this->option('dry-run');
        $preserveNames = (bool) $this->option('keep-names');
        $skipFile = $this->option('skip-file');
        $skipBeforeId = $this->option('skip-before-id');

        // Load skipped IDs from file if provided
        if ($skipFile) {
            $this->loadSkippedIds((string) $skipFile, $modelClass);
        }

        // Set minimum ID to skip
        if ($skipBeforeId) {
            $this->skipBeforeId = (int) $skipBeforeId;
            $this->info(sprintf('Will skip all records with ID <= %d', $this->skipBeforeId));
        }

        if (!class_exists($modelClass)) {
            $this->error(sprintf('Model class [%s] was not found.', $modelClass));

            return self::FAILURE;
        }

        if (!is_subclass_of($modelClass, Model::class)) {
            $this->error(sprintf('Class [%s] must extend %s.', $modelClass, Model::class));

            return self::FAILURE;
        }

        if (!method_exists($modelClass, 'imageAttributeNames')) {
            $this->error(sprintf('Model [%s] must use the HasImages trait.', $modelClass));

            return self::FAILURE;
        }

        if (!in_array($attribute, $modelClass::imageAttributeNames(), true)) {
            $this->error(sprintf('Attribute [%s] is not registered as an image collection on [%s].', $attribute, $modelClass));

            return self::FAILURE;
        }

        $sourceProvider = (string) ($this->option('source') ?: config('backpack-images.default_provider', 'local'));
        $targetProvider = (string) ($this->option('target') ?: config('backpack-images.default_provider', 'local'));
        $targetFolder = $this->option('folder');
        $targetFolder = $targetFolder !== null ? trim((string) $targetFolder, '/') : null;

        if ($sourceProvider === $targetProvider) {
            $this->error('Source and target providers must be different.');

            return self::INVALID;
        }

        try {
            $sourceProviderInstance = $uploader->getProvider($sourceProvider);
        } catch (Throwable $exception) {
            $this->error(sprintf('Unable to resolve source provider [%s]: %s', $sourceProvider, $exception->getMessage()));

            return self::FAILURE;
        }

        try {
            $uploader->getProvider($targetProvider);
        } catch (Throwable $exception) {
            $this->error(sprintf('Unable to resolve target provider [%s]: %s', $targetProvider, $exception->getMessage()));

            return self::FAILURE;
        }

        $targetOptions = $this->resolveTargetOptions($modelClass, $attribute, $targetProvider, $targetFolder, $uploader, $preserveNames);
        $sourceDisk = $this->resolveSourceDisk($sourceProvider);

        $this->info(sprintf(
            'Transferring [%s] images for %s (from %s to %s)%s',
            $attribute,
            $modelClass,
            $sourceProvider,
            $targetProvider,
            $dryRun ? ' [dry-run]' : ''
        ));

        $stats = [
            'records_scanned' => 0,
            'records_updated' => 0,
            'images_total' => 0,
            'uploads' => 0,
            'cached' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $modelClass::query();

        $query->chunkById($chunkSize, function ($models) use ($attribute, $sourceDisk, $uploader, $targetOptions, $sourceProviderInstance, $dryRun, $preserveNames, &$stats) {
            foreach ($models as $model) {
                $this->processModel($model, $attribute, $sourceDisk, $sourceProviderInstance, $uploader, $targetOptions, $dryRun, $preserveNames, $stats);
            }
            
            // Clear cache every chunk to free memory
            if (count($this->transferredCache) > 10000) {
                $this->transferredCache = array_slice($this->transferredCache, -5000, null, true);
            }
        });

        $this->info(sprintf(
            'Finished. Records scanned: %d, updated: %d, Images processed: %d, Uploaded: %d, Cached: %d, Skipped: %d, Failed: %d',
            $stats['records_scanned'],
            $stats['records_updated'],
            $stats['images_total'],
            $stats['uploads'],
            $stats['cached'],
            $stats['skipped'],
            $stats['failed']
        ));

        return self::SUCCESS;
    }

    protected function processModel(
        Model $model,
        string $attribute,
        ?string $sourceDisk,
        ImageStorageProvider $sourceProvider,
        ImageUploader $uploader,
        ImageUploadOptions $targetOptions,
        bool $dryRun,
        bool $preserveNames,
        array &$stats
    ): void {
        try {
            $modelKey = $model->getKey();

            // Skip if model ID is in the skipped list
            if (in_array($modelKey, $this->skippedIds, true)) {
                $stats['skipped']++;
                return;
            }

            // Skip if model ID is less than or equal to skip-before-id
            if ($this->skipBeforeId !== null && $modelKey <= $this->skipBeforeId) {
                $stats['skipped']++;
                return;
            }

            $images = $this->extractImages($model, $attribute);

            if (empty($images)) {
                return;
            }
        } catch (Throwable $exception) {
            $modelKey = $model->getKey() ?? 'unknown';
            $this->error(sprintf('Error processing %s #%s: %s', class_basename($model), $modelKey, $exception->getMessage()));
            $stats['failed']++;
            return;
        }

        $stats['records_scanned']++;

        $changed = false;

        foreach ($images as $index => $image) {
            try {
                $src = trim((string) ($image['src'] ?? ''));

                if ($src === '') {
                    $stats['skipped']++;
                    continue;
                }

                $stats['images_total']++;

                if (isset($this->transferredCache[$src])) {
                    $images[$index]['src'] = $this->transferredCache[$src];
                    $stats['cached']++;
                    $changed = true;
                    continue;
                }

                $stored = $this->transferSingleImage($src, $sourceDisk, $sourceProvider, $uploader, $targetOptions, $model, $attribute, $preserveNames);

                if (!$stored instanceof StoredImage) {
                    $stats['failed']++;
                    $this->warn(sprintf('Unable to transfer image "%s" for %s #%s', $src, get_class($model), $modelKey));
                    continue;
                }

                $images[$index]['src'] = $stored->path;
                $this->transferredCache[$src] = $stored->path;
                $stats['uploads']++;
                $changed = true;
                $this->line(sprintf('Transferred "%s" for %s #%s', $src, class_basename($model), $modelKey));
            } catch (Throwable $exception) {
                $src = $image['src'] ?? 'unknown';
                $this->error(sprintf('Error processing image "%s" for %s #%s: %s', $src, class_basename($model), $modelKey, $exception->getMessage()));
                $stats['failed']++;
                continue;
            }
        }

        if (!$changed) {
            return;
        }

        $stats['records_updated']++;

        if ($dryRun) {
            $this->line(sprintf('[dry-run] Would update %s #%s', class_basename($model), $modelKey));

            return;
        }

        try {
            $model->setAttribute($attribute, $images);

            if (method_exists($model, 'saveQuietly')) {
                $model->saveQuietly();
            } else {
                $model->save();
            }
        } catch (Throwable $exception) {
            $this->error(sprintf('Failed to save %s #%s: %s', class_basename($model), $modelKey, $exception->getMessage()));
            $stats['failed']++;
        }

        // Unset model to free memory
        unset($model);
    }

    protected function transferSingleImage(
        string $src,
        ?string $sourceDisk,
        ImageStorageProvider $sourceProvider,
        ImageUploader $uploader,
        ImageUploadOptions $targetOptions,
        Model $model,
        string $attribute,
        bool $preserveNames
    ): ?StoredImage {
        try {
            [$file, $cleanup] = $this->getSourceFile($src, $sourceDisk, $sourceProvider, $model, $attribute, $uploader, $preserveNames);

            if (!$file instanceof File) {
                return null;
            }

            try {
                return $uploader->uploadFromFile($file, $targetOptions);
            } catch (Throwable $exception) {
                $this->warn(sprintf('Upload failed for "%s": %s', $src, $exception->getMessage()));

                return null;
            } finally {
                if ($cleanup) {
                    try {
                        $cleanup();
                    } catch (Throwable) {
                        // Ignore cleanup errors
                    }
                }
            }
        } catch (Throwable $exception) {
            $this->warn(sprintf('Error preparing file for "%s": %s', $src, $exception->getMessage()));
            return null;
        }
    }

    /**
     * @return array{0: File|null, 1: callable|null}
     */
    protected function getSourceFile(
        string $src,
        ?string $sourceDisk,
        ImageStorageProvider $sourceProvider,
        Model $model,
        string $attribute,
        ImageUploader $uploader,
        bool $preserveNames
    ): array {
        if ($sourceDisk) {
            $file = $this->getFileFromDisk($sourceDisk, $src);

            if ($file instanceof File) {
                return [$file, null];
            }
        }

        $url = $this->resolveSourceUrl($src, $sourceProvider, $model, $attribute, $uploader);

        if (!$url) {
            return [null, null];
        }

        $desiredName = $preserveNames ? basename($src) : null;

        return $this->downloadToTemporaryFile($url, $desiredName);
    }

    protected function getFileFromDisk(string $disk, string $path): ?File
    {
        try {
            $storage = Storage::disk($disk);

            if (!$storage->exists($path)) {
                return null;
            }

            $absolutePath = $storage->path($path);

            if (!is_file($absolutePath)) {
                return null;
            }

            if (!is_readable($absolutePath)) {
                return null;
            }

            return new File($absolutePath);
        } catch (Throwable $exception) {
            $this->warn(sprintf('Error reading file from disk "%s" at path "%s": %s', $disk, $path, $exception->getMessage()));
            return null;
        }
    }

    /**
     * @return array{0: File|null, 1: callable|null}
     */
    protected function downloadToTemporaryFile(string $url, ?string $desiredName = null): array
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'img-transfer-');

        if ($tempPath === false) {
            return [null, null];
        }

        try {
            $response = Http::timeout(60)->connectTimeout(30)->retry(2, 100)->sink($tempPath)->get($url);

            if (!$response->successful()) {
                @unlink($tempPath);

                return [null, null];
            }
        } catch (Throwable $exception) {
            @unlink($tempPath);

            return [null, null];
        }

        if ($desiredName !== null) {
            $desiredName = basename(trim($desiredName));

            if ($desiredName !== '') {
                $targetPath = dirname($tempPath) . DIRECTORY_SEPARATOR . $desiredName;

                if (file_exists($targetPath)) {
                    $targetPath = dirname($tempPath) . DIRECTORY_SEPARATOR . uniqid('img-transfer-', true) . '-' . $desiredName;
                }

                if (@rename($tempPath, $targetPath)) {
                    $tempPath = $targetPath;
                }
            }
        }

        $cleanup = static function () use ($tempPath): void {
            @unlink($tempPath);
        };

        return [new File($tempPath), $cleanup];
    }

    protected function resolveSourceUrl(
        string $src,
        ImageStorageProvider $provider,
        Model $model,
        string $attribute,
        ImageUploader $uploader
    ): ?string {
        if ($this->isAbsoluteUrl($src)) {
            return $src;
        }

        try {
            return $provider->getUrl($src);
        } catch (Throwable) {
            if (method_exists($model, 'formatImageUrlForAttribute')) {
                return $model->formatImageUrlForAttribute($attribute, $src);
            }
        }

        return null;
    }

    protected function extractImages(Model $model, string $attribute): array
    {
        try {
            $value = $model->getAttribute($attribute);

            if ($value instanceof Collection) {
                $value = $value->toArray();
            }

            if (is_string($value)) {
                $decoded = json_decode($value, true);
                $value = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
            }

            if (!is_array($value)) {
                return [];
            }

            return array_values(array_map(static fn ($item) => is_array($item) ? $item : (array) $item, $value));
        } catch (Throwable $exception) {
            return [];
        }
    }

    protected function resolveTargetOptions(
        string $modelClass,
        string $attribute,
        string $targetProvider,
        ?string $folder,
        ImageUploader $uploader,
        bool $preserveNames
    ): ImageUploadOptions {
        if (method_exists($modelClass, 'imageUploadOptions')) {
            $options = $modelClass::imageUploadOptions($attribute);
        } else {
            $options = $uploader->getDefaultOptions();
        }

        $overrides = ['provider' => $targetProvider];

        if ($folder !== null && $folder !== '') {
            $overrides['folder'] = $folder;
        }

        if ($preserveNames) {
            $overrides['preserveOriginalName'] = true;
            $overrides['generateUniqueName'] = false;
        }

        return $options->withOverrides($overrides);
    }

    protected function resolveSourceDisk(string $providerName): ?string
    {
        $config = config("backpack-images.providers.{$providerName}");

        if (!is_array($config)) {
            return null;
        }

        $disk = $config['disk'] ?? null;

        return is_string($disk) && $disk !== '' ? $disk : null;
    }

    protected function resolveChunkSize(): int
    {
        $chunk = (int) ($this->option('chunk') ?? 100);

        return $chunk > 0 ? $chunk : 100;
    }

    protected function isAbsoluteUrl(string $value): bool
    {
        return str_starts_with($value, 'http://') || str_starts_with($value, 'https://');
    }

    /**
     * Load IDs of already processed models from a skip file.
     * File format: one entry per line with model name and ID, e.g. "Product #42"
     */
    protected function loadSkippedIds(string $skipFile, string $modelClass): void
    {
        if (!is_file($skipFile) || !is_readable($skipFile)) {
            $this->warn(sprintf('Skip file not found or not readable: %s', $skipFile));
            return;
        }

        $modelBaseName = class_basename($modelClass);
        $pattern = sprintf('/^.*%s\s+#(\d+)/', $modelBaseName);
        $skippedIds = [];

        try {
            $handle = fopen($skipFile, 'r');
            if ($handle === false) {
                $this->warn(sprintf('Unable to open skip file: %s', $skipFile));
                return;
            }

            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                if (preg_match($pattern, $line, $matches)) {
                    $skippedIds[] = (int) $matches[1];
                }
            }

            fclose($handle);
            $this->skippedIds = $skippedIds;
            $this->info(sprintf('Loaded %d skipped IDs from %s', count($this->skippedIds), $skipFile));
        } catch (Throwable $exception) {
            $this->warn(sprintf('Error reading skip file: %s', $exception->getMessage()));
        }
    }
}
