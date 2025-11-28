<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use Backpack\Store\app\Models\Product as BaseProduct;

use Backpack\Reviews\app\Traits\Reviewable;
use Backpack\Tag\app\Traits\Taggable;

use Dress\Translator\app\Interfaces\TranslatableInterface;
use Dress\Translator\app\Traits\TranslatableTrait;

// TRANSLATIONS
// use Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations;

class Product extends BaseProduct implements TranslatableInterface
{
    use Reviewable;
    use Taggable;
    // use HasTranslations;

    use TranslatableTrait;

    public static function setupTranslatableSettings(): void
    {
        // parent::setupTranslatableSettings();

        static::addCommonSettings([
            'title' => 'Товары',
            'key' => 'product',
            'identifier' => 'name',
            // 'backpack_key' => ''
            'backpack_title' => 'Товар',
            'backpack_settings' => [
                [
                    'type' => 'checkbox',
                    'name' => 'is_active_only',
                    'label' => 'Только активные товары',
                    'hint' => 'Переводить только для активных товаров'
                ],[
                    'type' => 'checkbox',
                    'name' => 'in_stock_only',
                    'label' => 'Только товары в наличии',
                    'hint' => 'Переводить только для товаров в наличии'
                ],[
                    'type' => 'number',
                    'name' => 'min_price',
                    'label' => 'Минимальная цена',
                    'hint' => 'Переводить только если цена товара выше или равна указанной'
                ]
            ],
            'translation_type' => 'auto',
            'languages' => ['ru', 'uk']
        ]);

        static::addTranslatableCase([
            'fields' => [
                'name' => 'Название товара',
            ],
            'driver' => 'deepl',
            'scopeName' => 'namesQuery',
        ]);

        static::addTranslatableCase([
            'fields' => [
                'content' => 'Описание товара'
            ],
            'driver' => 'deepl',
            'scopeName' => 'contentQuery',
        ]);


        static::addTranslatableCase([
            'fields' => [
                'extras_trans->custom_attrs' => 'Индивидуальные характеристики',
            ],
            'driver' => 'deepl',
            'scopeName' => 'customPropsQuery',
        ]);
    }

    public function getBackpackEditLinkAttribute(): string 
    {
        return backpack_url('product/' . $this->id . '/edit');
    }

    public function scopeTranslatableQuery(Builder $query, $settings) {
        $is_active_only = isset($settings['query']['is_active_only'])? (bool)$settings['query']['is_active_only']: false;
        $in_stock_only = isset($settings['query']['in_stock_only'])? (bool)$settings['query']['in_stock_only']: false;
        $min_price = isset($settings['query']['min_price']) && is_numeric($settings['query']['min_price'])? $settings['query']['min_price']: false;

        $query->when($is_active_only, function ($query) {
            $query->where('is_active', 1);
        })->whereHas('sp', function ($query) use ($in_stock_only, $min_price) {
            if ($in_stock_only) {
                $query->where('in_stock', '>', 0);
            }
            if ($min_price) {
                $query->where('price', '>=', $min_price);
            }
        });
    }

    public function scopeNamesQuery(Builder $query, $settings) {
        $from_langs = $settings['from_languages'];
        $min_symbols = 10;
        
        $query->where(function ($query) use ($from_langs, $min_symbols) {
            $conditions = [];
            foreach ($from_langs as $lang_key) {
                $json_key = str_replace('.', '\\.', $lang_key);
                $conditions[] = '(COALESCE(CHAR_LENGTH(JSON_UNQUOTE(JSON_EXTRACT(name, "$.' . $json_key . '"))) >= ' . $min_symbols . ', 0))';
            }
            $query->whereRaw('(' . implode(' + ', $conditions) . ') = 1');
        });
    }

    public function scopeContentQuery(Builder $query, $settings) {
        $from_langs = $settings['from_languages'];
        $min_symbols = 150;
        
        $query->where(function ($query) use ($from_langs, $min_symbols) {
            $conditions = [];
            foreach ($from_langs as $lang_key) {
                $json_key = str_replace('.', '\\.', $lang_key);
                $conditions[] = '(COALESCE(CHAR_LENGTH(JSON_UNQUOTE(JSON_EXTRACT(content, "$.' . $json_key . '"))) >= ' . $min_symbols . ', 0))';
            }
            $query->whereRaw('(' . implode(' + ', $conditions) . ') = 1');
        });
    }

    public function scopeCustomPropsQuery(Builder $query, $settings) {
        $from_langs = $settings['from_languages'];

        $query->whereNotNull('extras_trans')
                ->where('extras_trans', '!=', '')
                ->where(function($query) use($from_langs) {
                    $conditions = [];
                    foreach ($from_langs as $lang_key) {
                        $conditions[] = '(JSON_EXTRACT(extras_trans, "$.' . $lang_key . '.custom_attrs") IS NOT NULL AND JSON_UNQUOTE(JSON_EXTRACT(extras_trans, "$.' . $lang_key . '.custom_attrs")) != "null" AND JSON_UNQUOTE(JSON_EXTRACT(extras_trans, "$.' . $lang_key . '.custom_attrs")) != "")';
                    }
                    $query->whereRaw('(' . implode(' + ', $conditions) . ') = 1');
                });
    }
    // protected $morphClass = 'Backpack\Store\app\Models\Product';

    // public function getMorphClass()
    // {
    //     return 'Backpack\Store\app\Models\Product';
    // }
}
