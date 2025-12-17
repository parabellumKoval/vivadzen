<?php

namespace App\Settings;

use Backpack\Settings\Contracts\SettingsRegistrarInterface;
use Backpack\Settings\Services\Registry\Field;
use Backpack\Settings\Services\Registry\Registry;

class CoreSettingsRegistrar implements SettingsRegistrarInterface
{
    public function register(Registry $registry): void
    {
        $countryOptions = \Store::countryOptions();

        $registry->group('core', function ($group) use ($countryOptions) {
            $group->title('Основные')->icon('la la-cog')

                ->page('Email', function ($page) use ($countryOptions) {
                    $page->add(Field::make('core.order_emails.default', 'email')
                        ->label('Email по умолчанию')
                        ->hint('Используется, если не найдено правило для страны заказа.')
                        ->default('shop@vivadzen.com')
                        ->cast('string')
                        ->tab('Email')
                    );

                    $page->add(Field::make('core.order_emails.per_country', 'repeatable_pure')
                        ->label('Email для уведомлений по странам')
                        ->fields([
                            [
                                'name' => 'country',
                                'label' => 'Страна заказа',
                                'type' => 'select_from_array',
                                'options' => $countryOptions,
                                'allows_null' => false,
                            ],
                            [
                                'name' => 'email',
                                'label' => 'Email',
                                'type' => 'email',
                            ],
                        ])
                        ->newItemLabel('Добавить страну')
                        ->hint('Каждая строка переопределяет email уведомления для указанной страны.')
                        ->cast('array')
                        ->tab('Email')
                    );
                });
        });
    }
}
