<?php

namespace Backpack\Schedule\Settings;

use Backpack\Settings\Contracts\SettingsRegistrarInterface;
use Backpack\Settings\Services\Registry\Registry;
use Backpack\Settings\Services\Registry\Field;

class ScheduleSettingsRegistrar implements SettingsRegistrarInterface
{
    public function register(Registry $registry): void
    {
        $registry->group('schedule', function ($group) {
            $group->title('Планировщик публикаций')->icon('la la-clock')

                ->page('Основные настройки', function ($page) {
                    $page->add(Field::make('backpack.schedule.enabled', 'checkbox')
                        ->label('Включить модуль')
                        ->hint('Глобальное включение/выключение модуля отложенных публикаций')
                        ->cast('boolean')
                        ->default(true)
                        ->tab('Основное')
                    );

                    $page->add(Field::make('backpack.schedule.check_interval', 'number')
                        ->label('Интервал проверки (минуты)')
                        ->hint('Как часто проверять очередь публикаций. Рекомендуется 1-5 минут.')
                        ->cast('integer')
                        ->default(5)
                        ->attrs(['min' => 1, 'max' => 60])
                        ->tab('Основное')
                    );

                    $page->add(Field::make('backpack.schedule.batch_size', 'number')
                        ->label('Размер пакета')
                        ->hint('Максимальное количество записей для обработки за один запуск')
                        ->cast('integer')
                        ->default(100)
                        ->attrs(['min' => 1, 'max' => 1000])
                        ->tab('Основное')
                    );

                    $page->add(Field::make('backpack.schedule.default_overwrite_created_at', 'checkbox')
                        ->label('Перезаписывать дату создания по умолчанию')
                        ->hint('Значение по умолчанию для опции перезаписи даты создания при публикации')
                        ->cast('boolean')
                        ->default(false)
                        ->tab('Основное')
                    );
                })

                ->page('Модели', function ($page) {
                    $page->add(Field::make('backpack.schedule.models_list', 'repeatable')
                        ->label('Список моделей')
                        ->hint('Модели, которые поддерживают отложенную публикацию')
                        ->cast('array')
                        ->subfields([
                            [
                                'name' => 'model',
                                'type' => 'text',
                                'label' => 'Класс модели',
                                'hint' => 'Полный класс модели, например: App\\Models\\Post',
                                'wrapper' => ['class' => 'form-group col-md-4'],
                            ],
                            [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'Название',
                                'hint' => 'Человекочитаемое название для админки',
                                'wrapper' => ['class' => 'form-group col-md-3'],
                            ],
                            [
                                'name' => 'route',
                                'type' => 'text',
                                'label' => 'Роут CRUD',
                                'hint' => 'Название роута без префикса admin/, например: post',
                                'wrapper' => ['class' => 'form-group col-md-3'],
                            ],
                            [
                                'name' => 'publish_field',
                                'type' => 'text',
                                'label' => 'Поле публикации',
                                'hint' => 'Например: is_published, is_moderated',
                                'wrapper' => ['class' => 'form-group col-md-2'],
                            ],
                        ])
                        ->tab('Модели')
                    );
                })

                ->page('Уведомления', function ($page) {
                    $page->add(Field::make('backpack.schedule.notifications.enabled', 'checkbox')
                        ->label('Включить уведомления')
                        ->hint('Отправлять уведомления о публикациях')
                        ->cast('boolean')
                        ->default(false)
                        ->tab('Уведомления')
                    );

                    $page->add(Field::make('backpack.schedule.notifications.email', 'email')
                        ->label('Email для уведомлений')
                        ->hint('Адрес для получения уведомлений о публикациях')
                        ->cast('string')
                        ->tab('Уведомления')
                    );

                    $page->add(Field::make('backpack.schedule.notifications.on_publish', 'checkbox')
                        ->label('При успешной публикации')
                        ->cast('boolean')
                        ->default(false)
                        ->tab('Уведомления')
                    );

                    $page->add(Field::make('backpack.schedule.notifications.on_error', 'checkbox')
                        ->label('При ошибке публикации')
                        ->cast('boolean')
                        ->default(true)
                        ->tab('Уведомления')
                    );
                });
        });
    }
}
