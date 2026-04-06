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

                ->page('Партнеры', function ($page) {
                    $page->add(Field::make('site.contacts.partners', 'repeatable_pure')
                        ->label('Партнерские магазины')
                        ->fields([
                            [
                                'name' => 'name',
                                'label' => 'Название магазина',
                                'type' => 'text',
                            ],
                            [
                                'name' => 'city',
                                'label' => 'Город',
                                'type' => 'text',
                            ],
                            [
                                'name' => 'address',
                                'label' => 'Адрес',
                                'type' => 'text',
                            ],
                            [
                                'name' => 'schedule',
                                'label' => 'Время работы',
                                'type' => 'text',
                            ],
                            [
                                'name' => 'phone',
                                'label' => 'Телефон',
                                'type' => 'text',
                            ],
                            [
                                'name' => 'email',
                                'label' => 'Email',
                                'type' => 'email',
                            ],
                            [
                                'name' => 'map',
                                'label' => 'Google Map',
                                'type' => 'textarea',
                            ],
                        ])
                        ->newItemLabel('Добавить магазин')
                        ->hint('Обязательные поля: название магазина, город и адрес. В Google Map можно вставить ссылку или iframe-код.')
                        ->cast('array')
                        ->translatable(true)
                        ->regionable(true)
                        ->tab('Партнерские магазины')
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
                })

                ->page('Главная', function ($page) {
                    $page->add(Field::make('site.home.sections.banner', 'checkbox')
                        ->label('Показывать баннер (верхний hero)')
                        ->default(true)
                        ->cast('bool')
                        ->regionable(true)
                        ->tab('Секции')
                    );

                    $page->add(Field::make('site.home.sections.category', 'checkbox')
                        ->label('Показывать блок категорий')
                        ->default(true)
                        ->cast('bool')
                        ->regionable(true)
                        ->tab('Секции')
                    );

                    $page->add(Field::make('site.home.sections.lists_main', 'checkbox')
                        ->label('Показывать блок подборок товаров')
                        ->default(true)
                        ->cast('bool')
                        ->regionable(true)
                        ->tab('Секции')
                    );

                    $page->add(Field::make('site.home.sections.about', 'checkbox')
                        ->label('Показывать блок "О нас"')
                        ->default(true)
                        ->cast('bool')
                        ->regionable(true)
                        ->tab('Секции')
                    );

                    $page->add(Field::make('site.home.sections.review_video', 'checkbox')
                        ->label('Показывать блок видео-отзывов')
                        ->default(true)
                        ->cast('bool')
                        ->regionable(true)
                        ->tab('Секции')
                    );

                    $page->add(Field::make('site.home.sections.referral', 'checkbox')
                        ->label('Показывать реферальный блок')
                        ->default(true)
                        ->cast('bool')
                        ->regionable(true)
                        ->tab('Секции')
                    );

                    $page->add(Field::make('site.home.sections.vivapoints', 'checkbox')
                        ->label('Показывать блок VivaPoints')
                        ->default(true)
                        ->cast('bool')
                        ->regionable(true)
                        ->tab('Секции')
                    );

                    $page->add(Field::make('site.home.sections.mobile_sidebar', 'checkbox')
                        ->label('Показывать мобильный sidebar (категории + статьи)')
                        ->default(true)
                        ->cast('bool')
                        ->regionable(true)
                        ->tab('Секции')
                    );

                    $page->add(Field::make('site.home.sections.affiliate_link', 'checkbox')
                        ->label('Показывать floating кнопку affiliate (`lazy-affiliate-link`)')
                        ->default(true)
                        ->cast('bool')
                        ->regionable(true)
                        ->tab('Дополнительно')
                    );
                });
        });
    }
}
