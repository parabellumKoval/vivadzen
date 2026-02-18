<?php

namespace App\Settings;

use Backpack\Settings\Contracts\SettingsRegistrarInterface;
use Backpack\Settings\Services\Registry\Registry;
use Backpack\Settings\Services\Registry\Field;

class SiteSettingsRegistrar implements SettingsRegistrarInterface
{
    public function register(Registry $registry): void
    {
        $registry->group('site', function ($group) {
            $group->title('Глобальные настройки')->icon('la la-store')

                ->page('Контакты', function ($page) {
                    $page->add(Field::make('site.contacts.phone', 'text')
                        ->label('Номер телефона')
                        ->cast('string')
                        ->regionable(true)
                        ->tab('Основное')
                    );
                    
                    $page->add(Field::make('site.contacts.email', 'text')
                        ->label('Email')
                        ->cast('string')
                        ->regionable(true)
                        ->tab('Основное')
                    );
                    
                    $page->add(Field::make('site.contacts.address', 'text')
                        ->label('Адрес')
                        ->cast('string')
                        ->regionable(true)
                        ->translatable(true)
                        ->tab('Основное')
                    );

                    $page->add(Field::make('site.contacts.schedule', 'text')
                        ->label('График работы')
                        ->cast('string')
                        ->regionable(true)
                        ->translatable(true)
                        ->tab('Основное')
                    );

                    $page->add(Field::make('site.contacts.map', 'text')
                        ->label('Код карты')
                        ->cast('string')
                        ->regionable(true)
                        ->tab('Основное')
                    );
                    $page->add(Field::make('site.contacts.social.viber', 'text')
                        ->label('Viber')
                        ->cast('string')
                        ->regionable(true)
                        ->tab('Мессенджеры')
                    );
                    $page->add(Field::make('site.contacts.social.whatsapp', 'text')
                        ->label('Whatsapp')
                        ->cast('string')
                        ->regionable(true)
                        ->tab('Мессенджеры')
                    );
                    $page->add(Field::make('site.contacts.social.telegram', 'text')
                        ->label('Telegram')
                        ->cast('string')
                        ->regionable(true)
                        ->tab('Мессенджеры')
                    );
                    $page->add(Field::make('site.contacts.social.instagram', 'text')
                        ->label('Instagram')
                        ->cast('string')
                        ->regionable(true)
                        ->tab('Социальные сети')
                    );
                    $page->add(Field::make('site.contacts.social.facebook', 'text')
                        ->label('Facebook')
                        ->cast('string')
                        ->regionable(true)
                        ->tab('Социальные сети')
                    );
                    $page->add(Field::make('site.contacts.social.youtube', 'text')
                        ->label('Youtube')
                        ->cast('string')
                        ->regionable(true)
                        ->tab('Социальные сети')
                    );
                })

                ->page('Основное', function ($page) {
                    $page->add(Field::make('site.common.description', 'ckeditor')
                        ->label('Описание')
                        ->cast('string')
                        ->translatable(true)
                        ->tab('Основное')
                    );
                })
                
                ->page('Дополнительно', function ($page) {
                    $page->add(Field::make('site.common.supheader', 'ckeditor')
                        ->label('Верхняя строка')
                        ->cast('string')
                        ->translatable(true)
                        ->regionable(true)
                        ->hint('Текст в верхней строке.')
                        ->tab('Основное')
                    );
                });
        });
    }
}
