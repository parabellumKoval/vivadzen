<?php

namespace App\Console\Commands\Import;

use App\Models\StoreProduct;
use Illuminate\Console\Command;

class ImportEsProductTranslations extends Command
{
    protected $signature = 'import:products-es-translations
                            {path? : Путь к CSV (по умолчанию public/csv/es-products.csv)}
                            {--delimiter=, : Разделитель CSV}
                            {--dry-run : Только показать изменения без сохранения}';

    protected $description = 'Импорт испанских переводов для товаров по old_id из CSV es-products.csv';

    private const DEFAULT_RELATIVE_PATH = 'public/csv/es-products.csv';
    private const TARGET_LANG = 'es';

    public function handle(): int
    {
        $delimiter = (string) $this->option('delimiter');
        $dryRun = (bool) $this->option('dry-run');
        $path = $this->resolvePath((string) $this->argument('path'));

        if (! is_readable($path)) {
            $this->error("Файл не найден или недоступен: {$path}");
            return self::FAILURE;
        }

        $this->info(sprintf(
            'Импорт переводов из %s%s',
            $path,
            $dryRun ? ' (DRY-RUN)' : ''
        ));

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            $this->error("Не удалось открыть файл: {$path}");
            return self::FAILURE;
        }

        $header = null;
        $lineNumber = 0;
        $totalRows = 0;
        $matched = 0;
        $updated = 0;
        $skipped = 0;
        $notFound = 0;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $lineNumber++;

            if ($header === null) {
                $header = $this->normalizeHeader($row);
                continue;
            }

            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $totalRows++;
            $record = $this->combineRow($header, $row);
            $legacyId = $this->normalizeId($record['ID'] ?? null);

            if ($legacyId === null) {
                $skipped++;
                $this->warn("[{$lineNumber}] Пропуск: пустой ID");
                continue;
            }

            /** @var StoreProduct|null $product */
            $product = StoreProduct::query()
                ->where('old_id', $legacyId)
                ->orderBy('id')
                ->first();

            if (! $product) {
                $notFound++;
                $this->warn("[{$lineNumber}] Товар с old_id {$legacyId} не найден");
                continue;
            }

            $matched++;
            $changes = $this->applyTranslations($product, $record);

            if ($changes === []) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line(sprintf(
                    '[%d] #%d (old_id=%s) → %s',
                    $lineNumber,
                    $product->id,
                    $legacyId,
                    implode(', ', $changes)
                ));
            } else {
                $product->save();
                $this->line(sprintf(
                    '[%d] #%d (old_id=%s) обновлён: %s',
                    $lineNumber,
                    $product->id,
                    $legacyId,
                    implode(', ', $changes)
                ));
            }

            $updated++;
        }

        fclose($handle);

        $this->info(sprintf(
            'Готово. Строк обработано: %d, найдено товаров: %d, обновлено: %d, пропущено: %d, не найдено: %d',
            $totalRows,
            $matched,
            $updated,
            $skipped,
            $notFound
        ));

        return self::SUCCESS;
    }

    /**
     * @param  array<string, string|null>  $record
     * @return array<int, string>
     */
    private function applyTranslations(StoreProduct $product, array $record): array
    {
        $lang = self::TARGET_LANG;
        $changes = [];

        $name = $this->normalizeSingleLine($record['Название'] ?? null);
        if ($name !== null && $product->getTranslation('name', $lang, false) !== $name) {
            $product->setTranslation('name', $lang, $name);
            $changes[] = 'name';
        }

        $excerpt = $this->normalizeRichText($record['Короткое описание'] ?? null);
        if ($excerpt !== null && $product->getTranslation('excerpt', $lang, false) !== $excerpt) {
            $product->setTranslation('excerpt', $lang, $excerpt);
            $changes[] = 'excerpt';
        }

        $content = $this->normalizeRichText($record['Длинное описание'] ?? null);
        if ($content !== null && $product->getTranslation('content', $lang, false) !== $content) {
            $product->setTranslation('content', $lang, $content);
            $changes[] = 'content';
        }

        $seoPatch = array_filter([
            'meta_title' => $this->normalizeSingleLine($record['Meta title'] ?? null),
            'meta_description' => $this->normalizeSingleLine($record['Meta description'] ?? null),
        ], static fn ($value) => $value !== null);

        if ($seoPatch !== []) {
            $currentSeo = $product->getTranslation('seo', $lang, false);
            $currentSeo = is_array($currentSeo) ? $currentSeo : [];
            $newSeo = array_replace($currentSeo, $seoPatch);
            if ($newSeo !== $currentSeo) {
                $product->setTranslation('seo', $lang, $newSeo);
                $changes[] = 'seo';
            }
        }

        $extrasPatch = $this->buildExtrasPatch($record);
        if ($extrasPatch !== []) {
            $extras = $this->currentExtras($product);
            $currentEs = isset($extras[$lang]) && is_array($extras[$lang]) ? $extras[$lang] : [];
            $newEs = array_replace($currentEs, $extrasPatch);

            if ($newEs !== $currentEs) {
                $extras[$lang] = $newEs;
                $product->extras = $extras;
                $changes[] = 'extras';
            }
        }

        return $changes;
    }

    /**
     * @param  array<string, string|null>  $record
     * @return array<string, mixed>
     */
    private function buildExtrasPatch(array $record): array
    {
        $tags = $this->extractList($record['Теги'] ?? null);
        $primary = $this->normalizeSingleLine($record['Основной ключ'] ?? null);
        $secondary = $this->extractList($record['Вторичные ключи'] ?? null);
        $lsi = $this->extractList($record['LSI ключи'] ?? null);

        return array_filter([
            'tags' => $tags,
            'primary_keyword' => $primary,
            'secondary_keywords' => $secondary,
            'lsi_keywords' => $lsi,
        ], static function ($value) {
            if (is_array($value)) {
                return $value !== [];
            }

            return $value !== null;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function currentExtras(StoreProduct $product): array
    {
        $extras = $product->extras;

        if (is_array($extras)) {
            return $extras;
        }

        if (is_string($extras)) {
            $decoded = json_decode($extras, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * @param  array<int, string|null>  $header
     * @param  array<int, string|null>  $row
     * @return array<string, string|null>
     */
    private function combineRow(array $header, array $row): array
    {
        $result = [];

        foreach ($header as $index => $column) {
            if ($column === '') {
                continue;
            }

            $result[$column] = $row[$index] ?? null;
        }

        return $result;
    }

    /**
     * @param  array<int, string|null>  $row
     * @return array<int, string>
     */
    private function normalizeHeader(array $row): array
    {
        return array_map(function ($value) {
            $value = $value ?? '';
            $value = trim($value);

            if (str_starts_with($value, "\u{FEFF}")) {
                $value = ltrim($value, "\u{FEFF}");
            }

            return $value;
        }, $row);
    }

    /**
     * @param  array<int, string|null>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function resolvePath(?string $argument): string
    {
        if ($argument && trim($argument) !== '') {
            $path = $argument;
            if (! str_starts_with($path, DIRECTORY_SEPARATOR) && ! preg_match('/^[A-Za-z]:\\\\/', $path)) {
                return base_path($path);
            }

            return $path;
        }

        return base_path(self::DEFAULT_RELATIVE_PATH);
    }

    private function normalizeId(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function normalizeSingleLine(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim(preg_replace('/\s+/u', ' ', str_replace(["\r\n", "\r", "\n"], ' ', (string) $value)));

        return $normalized === '' ? null : $normalized;
    }

    private function normalizeRichText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = str_replace("\r\n", "\n", (string) $value);
        $value = str_replace("\r", "\n", $value);
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * @return array<int, string>
     */
    private function extractList(?string $value): array
    {
        if ($value === null) {
            return [];
        }

        $value = str_replace("\r\n", "\n", (string) $value);
        $value = str_replace("\r", "\n", $value);
        $value = trim($value);

        if ($value === '') {
            return [];
        }

        $parts = preg_split('/[,\\n]+/u', $value);

        if (! is_array($parts)) {
            return [];
        }

        $items = array_map(static fn ($item) => trim((string) $item), $parts);
        $items = array_filter($items, static fn ($item) => $item !== '');

        return array_values(array_unique($items));
    }
}
