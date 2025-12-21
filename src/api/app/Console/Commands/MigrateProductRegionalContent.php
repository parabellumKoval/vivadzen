<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductRegionalContent;
use Illuminate\Console\Command;

class MigrateProductRegionalContent extends Command
{
    protected $signature = 'store:migrate-regional-content
        {--countries=* : Ограничить список стран (коды a2)}
        {--chunk=200 : Размер чанка выборки}
        {--force : Перезаписывать уже заполненные переводы}
        {--dry-run : Только подсчитать, без сохранения}';

    protected $description = 'Переносит глобальные поля товара (content, excerpt, merchant_content) в таблицу ak_product_regional_contents';

    public function handle(): int
    {
        $countryMap = $this->buildCountryLocaleMap();

        if (! empty($countryMap)) {
            $filter = $this->normalizeCountryInput($this->option('countries'));

            if ($filter !== []) {
                $countryMap = array_intersect_key($countryMap, array_flip($filter));

                if ($countryMap === []) {
                    $this->error('Не найдено совпадений для заданных стран.');
                    return self::FAILURE;
                }
            }
        }

        if ($countryMap === []) {
            $this->error('В конфиге multistore не указаны страны с полем locale. Нечего мигрировать.');
            return self::FAILURE;
        }

        $chunkSize = (int) $this->option('chunk') ?: 200;
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        $totalProducts = Product::query()->count();
        $bar = $this->output->createProgressBar($totalProducts);
        $bar->start();

        $updatedRows = 0;
        $touchedProducts = 0;

        Product::query()
            ->select(['id', 'content', 'excerpt', 'merchant_content'])
            ->with('regionalContents')
            ->chunkById($chunkSize, function ($products) use ($countryMap, $force, $dryRun, &$updatedRows, &$touchedProducts, $bar) {
                foreach ($products as $product) {
                    /** @var Product $product */
                    $result = $this->migrateProduct($product, $countryMap, $force, $dryRun);
                    $updatedRows += $result['rows'];

                    if ($result['rows'] > 0) {
                        $touchedProducts++;
                    }

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);

        if ($dryRun) {
            $this->info(sprintf(
                'Dry-run завершён. Было бы обновлено строк: %d (товаров: %d).',
                $updatedRows,
                $touchedProducts
            ));
        } else {
            $this->info(sprintf(
                'Готово. Обновлено строк: %d. Затронуто товаров: %d.',
                $updatedRows,
                $touchedProducts
            ));
        }

        return self::SUCCESS;
    }

    protected function migrateProduct(Product $product, array $countryMap, bool $force, bool $dryRun): array
    {
        $rowsUpdated = 0;
        $contentTranslations = $product->getTranslations('content');
        $excerptTranslations = $product->getTranslations('excerpt');
        $merchantTranslations = $product->getTranslations('merchant_content');

        $existingRegional = $product->relationLoaded('regionalContents')
            ? $product->regionalContents
            : collect();

        foreach ($countryMap as $countryCode => $locale) {
            $contentValue = $this->normalizeTextValue($contentTranslations[$locale] ?? null);
            $excerptValue = $this->normalizeTextValue($excerptTranslations[$locale] ?? null);
            $merchantValue = $this->normalizeTextValue($merchantTranslations[$locale] ?? null);

            if ($contentValue === null && $excerptValue === null && $merchantValue === null) {
                continue;
            }

            /** @var ProductRegionalContent|null $regional */
            $regional = $existingRegional
                ->firstWhere('country_code', $countryCode);

            if (!$regional) {
                $regional = new ProductRegionalContent([
                    'product_id' => $product->id,
                    'country_code' => $countryCode,
                ]);
            }

            $changed = false;

            if ($contentValue !== null) {
                $changed = $this->setTranslationValue($regional, 'content', $locale, $contentValue, $force) || $changed;
            }

            if ($excerptValue !== null) {
                $changed = $this->setTranslationValue($regional, 'excerpt', $locale, $excerptValue, $force) || $changed;
            }

            if ($merchantValue !== null) {
                $changed = $this->setTranslationValue($regional, 'merchant_content', $locale, $merchantValue, $force) || $changed;
            }

            if (! $changed) {
                continue;
            }

            $rowsUpdated++;

            if (! $dryRun) {
                $regional->product_id = $product->id;
                $regional->country_code = $countryCode;
                $regional->save();
            }
        }

        return [
            'rows' => $rowsUpdated,
        ];
    }

    protected function setTranslationValue(ProductRegionalContent $regional, string $attribute, string $locale, string $value, bool $force): bool
    {
        $translations = $regional->getTranslations($attribute);
        $current = $this->normalizeTextValue($translations[$locale] ?? null);

        if ($current !== null && ! $force) {
            return false;
        }

        $translations[$locale] = $value;
        $regional->setTranslations($attribute, $translations);

        return true;
    }

    protected function normalizeTextValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            return $trimmed === '' ? null : $trimmed;
        }

        if (is_array($value) || is_object($value)) {
            $json = json_encode($value, JSON_UNESCAPED_UNICODE);
            $trimmed = trim((string) $json);
            return $trimmed === '' ? null : $trimmed;
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return null;
    }

    protected function buildCountryLocaleMap(): array
    {
        $countries = (array) \Store::countries();

        $map = [];

        foreach ($countries as $code => $config) {
            $normalizedCode = $this->normalizeCountryCode($code);
            $locale = $config['locale'] ?? null;
            $normalizedLocale = is_string($locale) ? trim($locale) : '';

            if (! $normalizedCode || $normalizedLocale === '') {
                continue;
            }

            $map[$normalizedCode] = $normalizedLocale;
        }

        return $map;
    }

    protected function normalizeCountryInput($input): array
    {
        $values = [];

        foreach ((array) $input as $item) {
            $code = $this->normalizeCountryCode($item);

            if ($code) {
                $values[] = $code;
            }
        }

        return array_values(array_unique($values));
    }

    protected function normalizeCountryCode($code): ?string
    {
        if (!is_string($code)) {
            return null;
        }

        $trimmed = strtolower(trim($code));

        return $trimmed === '' ? null : $trimmed;
    }
}
