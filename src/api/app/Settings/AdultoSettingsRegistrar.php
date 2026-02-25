<?php

namespace App\Settings;

use Backpack\Settings\Contracts\SettingsRegistrarInterface;
use Backpack\Settings\Services\Registry\Field;
use Backpack\Settings\Services\Registry\Registry;

class AdultoSettingsRegistrar implements SettingsRegistrarInterface
{
    public function register(Registry $registry): void
    {
        $registry->group('age-verification', function ($group) {
            $group->title('18+ Проверка')->icon('la la-user-shield')
                ->page('ADULTO.cz', function ($page) {
                    $page->add(Field::make('store.age_verification.adulto.enabled', 'checkbox')
                        ->label('Включить проверку возраста через ADULTO.cz')
                        ->default(false)
                        ->cast('bool')
                        ->tab('Основное')
                    );

                    $page->add(Field::make('store.age_verification.adulto.category_ids', 'select2_from_array')
                        ->label('Категории товаров 18+')
                        ->options($this->categoryOptions())
                        ->allowsMultiple(true)
                        ->cast('array')
                        ->hint('Проверка применяется только для региона /cz и только для выбранных категорий (включая дочерние).')
                        ->tab('Основное')
                    );
                });
        });
    }

    protected function categoryOptions(): array
    {
        $categoryClass = \Settings::get('dress.category.model_admin', \Backpack\Store\app\Models\Category::class);

        if (!is_string($categoryClass) || !class_exists($categoryClass)) {
            return [];
        }

        try {
            $categories = $categoryClass::query()
                ->select(['id', 'parent_id', 'name'])
                ->orderBy('lft')
                ->get();
        } catch (\Throwable $e) {
            try {
                $categories = $categoryClass::query()
                    ->select(['id', 'parent_id', 'name'])
                    ->orderBy('id')
                    ->get();
            } catch (\Throwable $fallbackException) {
                return [];
            }
        }
        $byId = $categories->keyBy('id');
        $options = [];

        foreach ($categories as $category) {
            $names = [];
            $current = $category;
            $guard = 0;

            while ($current && $guard < 100) {
                array_unshift($names, (string) ($current->name ?? ''));
                $parentId = $current->parent_id ? (int) $current->parent_id : null;
                $current = $parentId ? $byId->get($parentId) : null;
                $guard++;
            }

            $path = implode(' -> ', array_filter($names));
            $label = sprintf('id: %d -> %s', (int) $category->id, $path ?: (string) $category->id);
            $options[(string) $category->id] = $label;
        }

        return $options;
    }
}
