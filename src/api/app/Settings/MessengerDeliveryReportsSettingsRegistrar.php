<?php

namespace App\Settings;

use Backpack\Settings\Contracts\SettingsRegistrarInterface;
use Backpack\Settings\Services\Registry\Field;
use Backpack\Settings\Services\Registry\Registry;

class MessengerDeliveryReportsSettingsRegistrar implements SettingsRegistrarInterface
{
    public function register(Registry $registry): void
    {
        $registry->group('delivery-reporting', function ($group) {
            $group->title('Delivery Reporting')->icon('la la-shipping-fast')
                ->page('Messenger.cz', function ($page) {
                    $page->add(Field::make('shipping.messenger.reporting.api_key', 'text')
                        ->label('API key')
                        ->hint('Ключ должен совпадать с заголовком X-API-KEY, который отправляет Messenger.cz. Endpoint: /api/delivery-reports/messenger')
                        ->cast('string')
                        ->tab('Webhook')
                    );

                    $page->add(Field::make('shipping.messenger.reporting.apply_delivery_status', 'checkbox')
                        ->label('Обновлять delivery_status у заказа')
                        ->default(true)
                        ->cast('bool')
                        ->tab('Статусы заказа')
                    );

                    $page->add(Field::make('shipping.messenger.reporting.apply_pay_status', 'checkbox')
                        ->label('Обновлять pay_status у заказа')
                        ->default(true)
                        ->cast('bool')
                        ->tab('Статусы заказа')
                    );

                    $page->add(Field::make('shipping.messenger.reporting.apply_order_status', 'checkbox')
                        ->label('Обновлять общий status у заказа')
                        ->default(true)
                        ->cast('bool')
                        ->tab('Статусы заказа')
                    );
                });
        });
    }
}
