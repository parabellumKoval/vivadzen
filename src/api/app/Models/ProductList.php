<?php

namespace App\Models;

use Backpack\Store\app\Models\ProductList as BaseProductList;
use Dress\Translator\app\Interfaces\TranslatableInterface;
use Dress\Translator\app\Traits\TranslatableTrait;
use Illuminate\Database\Eloquent\Builder;

class ProductList extends BaseProductList implements TranslatableInterface
{
    use TranslatableTrait;

    public static function setupTranslatableSettings(): void
    {
        static::addCommonSettings([
            'title' => 'Товарные списки',
            'key' => 'product_list',
            'identifier' => 'name',
            'backpack_title' => 'Товарные списки',
            'backpack_settings' => [
                [
                    'type' => 'checkbox',
                    'name' => 'is_active_only',
                    'label' => 'Только активные списки',
                    'hint' => 'Переводить только списки со статусом «Активен»',
                ],
            ],
        ]);

        static::addTranslatableCase([
            'fields' => [
                'title' => 'Заголовок списка',
                'button_text' => 'Текст кнопки',
            ],
            'driver' => 'deepl',
        ]);
    }

    public function scopeTranslatableQuery(Builder $query, $settings)
    {
        $isActiveOnly = isset($settings['query']['is_active_only'])
            ? (bool) $settings['query']['is_active_only']
            : false;

        if ($isActiveOnly) {
            $query->where('is_active', 1);
        }

        return $query;
    }

    public function getBackpackEditLinkAttribute(): string
    {
        return backpack_url('product-list/' . $this->id . '/edit');
    }
}
