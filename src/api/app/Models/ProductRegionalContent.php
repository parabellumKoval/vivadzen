<?php

namespace App\Models;

use Backpack\Store\app\Models\ProductRegionalContent as BaseProductRegionalContent;
use Dress\Translator\app\Interfaces\TranslatableInterface;
use Dress\Translator\app\Traits\TranslatableTrait;
use Illuminate\Database\Eloquent\Builder;

class ProductRegionalContent extends BaseProductRegionalContent implements TranslatableInterface
{
    use TranslatableTrait;

    protected $translatable = ['content', 'excerpt', 'merchant_content'];

    /**
     * Матрица языков в зависимости от страны записи.
     */
    protected const COUNTRY_LANGUAGE_MATRIX = [
        'ES' => [
            'from_languages' => ['es'],
            'to_languages' => ['ru', 'uk', 'en'],
        ],
        'UA' => [
            'from_languages' => ['uk'],
            'to_languages' => ['ru'],
        ],
        'CZ' => [
            'from_languages' => ['cs'],
            'to_languages' => ['uk', 'ru', 'en'],
        ],
        'DE' => [
            'from_languages' => ['de'],
            'to_languages' => ['uk', 'ru', 'en'],
        ],
    ];

    /**
     * Языки по-умолчанию, если страна не указана в матрице.
     */
    protected const DEFAULT_LANGUAGES = [
        'from_languages' => ['ru', 'uk', 'en'],
        'to_languages' => ['ru', 'uk', 'en'],
    ];

    public static function setupTranslatableSettings(): void
    {
        static::addCommonSettings([
            'title' => 'Региональный контент товаров',
            'key' => 'product_regional_content',
            'identifier' => 'uniq_string',
            'backpack_title' => 'Региональный контент',
            'backpack_settings' => [],
            'translation_type' => 'auto',
            'languages_managed_by_model' => true,
            'from_languages' => self::DEFAULT_LANGUAGES['from_languages'],
            'to_languages' => self::DEFAULT_LANGUAGES['to_languages'],
        ]);

        static::addTranslatableCase([
            'fields' => [
                'content' => 'Контент',
                'excerpt' => 'Короткое описание',
                'merchant_content' => 'Описание мерчанта',
            ],
            'driver' => 'deepl',
        ]);
    }

    public function getBackpackEditLinkAttribute(): string
    {
        return backpack_url('product/' . $this->product_id . '/edit');
    }

    public function scopeTranslatableQuery(Builder $query, $settings)
    {
        return $query;
    }

    public function resolveTranslatorLanguages(array $settings): array
    {
        $countryCode = strtoupper((string) ($this->country_code ?? ''));
        $matrix = self::COUNTRY_LANGUAGE_MATRIX[$countryCode] ?? null;

        $fallbackFrom = $settings['from_languages'] ?? self::DEFAULT_LANGUAGES['from_languages'];
        $fallbackTo = $settings['to_languages'] ?? self::DEFAULT_LANGUAGES['to_languages'];

        return [
            'from_languages' => $this->normalizeLanguages($matrix['from_languages'] ?? $fallbackFrom, $fallbackFrom),
            'to_languages' => $this->normalizeLanguages($matrix['to_languages'] ?? $fallbackTo, $fallbackTo),
        ];
    }

    /**
     * Приводит список языков к уникальному и непустому массиву.
     */
    protected function normalizeLanguages($languages, array $fallback = []): array
    {
        $prepared = is_array($languages) ? $languages : [];
        $normalized = array_values(array_unique(array_filter($prepared, function ($value) {
            return $value !== null && $value !== '';
        })));

        if (!empty($normalized)) {
            return $normalized;
        }

        $fallbackNormalized = array_values(array_unique(array_filter($fallback, function ($value) {
            return $value !== null && $value !== '';
        })));

        return $fallbackNormalized;
    }
}
