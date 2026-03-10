<?php

namespace App\Models;

use Backpack\Store\app\Models\FaqTemplate as BaseFaqTemplate;
use Dress\Translator\app\Interfaces\TranslatableInterface;
use Dress\Translator\app\Traits\TranslatableTrait;
use Illuminate\Database\Eloquent\Builder;

class FaqTemplate extends BaseFaqTemplate implements TranslatableInterface
{
    use TranslatableTrait;

    protected $translatable = [
        'name',
        'extras_trans',
        'extras_trans->faq_items',
    ];

    public static function setupTranslatableSettings(): void
    {
        static::addCommonSettings([
            'title' => 'FAQ шаблоны',
            'key' => 'faq_template',
            'identifier' => 'name',
            'backpack_title' => 'FAQ шаблоны',
            'backpack_settings' => [
                [
                    'type' => 'checkbox',
                    'name' => 'is_active_only',
                    'label' => 'Только активные FAQ шаблоны',
                    'hint' => '',
                ],
            ],
        ]);

        static::addTranslatableCase([
            'fields' => [
                'extras_trans->faq_items' => 'FAQ пункты',
            ],
            'driver' => 'deepl',
            'scopeName' => 'faqItemsQuery',
        ]);
    }

    public function scopeTranslatableQuery(Builder $query, $settings): void
    {
        $is_active_only = isset($settings['query']['is_active_only'])
            ? (bool) $settings['query']['is_active_only']
            : false;

        if ($is_active_only) {
            $query->where('is_active', 1);
        }
    }

    public function scopeFaqItemsQuery(Builder $query, $settings): void
    {
        $from_langs = $settings['from_languages'] ?? [];

        if (empty($from_langs)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->whereNotNull('extras_trans')
            ->where('extras_trans', '!=', '')
            ->where(function (Builder $query) use ($from_langs) {
                foreach ($from_langs as $index => $lang_key) {
                    $json_key = str_replace('.', '\\.', (string) $lang_key);
                    $json_path = '$.' . $json_key . '.faq_items';

                    $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                    $query->{$method}(
                        '(JSON_TYPE(JSON_EXTRACT(extras_trans, ?)) = "ARRAY" AND JSON_LENGTH(JSON_EXTRACT(extras_trans, ?)) > 0)',
                        [$json_path, $json_path]
                    );
                }
            });
    }

    public function getBackpackEditLinkAttribute(): string
    {
        return backpack_url('faq-template/' . $this->id . '/edit');
    }
}
