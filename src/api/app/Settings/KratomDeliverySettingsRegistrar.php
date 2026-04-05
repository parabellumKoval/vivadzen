<?php

namespace App\Settings;

use Backpack\Settings\Contracts\SettingsRegistrarInterface;
use Backpack\Settings\Services\Registry\Field;
use Backpack\Settings\Services\Registry\Registry;
use Backpack\Store\app\Support\CheckoutMethodCatalog;

class KratomDeliverySettingsRegistrar implements SettingsRegistrarInterface
{
    public function register(Registry $registry): void
    {
        $registry->group('delivery', function ($group) {
            $group->page('Кратом', function ($page) {
                $deliveries = CheckoutMethodCatalog::deliveryMethods();

                $options = array_reduce($deliveries, function ($carry, $item) {
                    $key = $item['name'] . '_' . $item['type'];
                    $carry[$key] = $item['label'];
                    return $carry;
                }, []);

                $page->add(Field::make('shipping.kratom.add_to_order_enabled', 'checkbox')
                    ->label('Добавлять стоимость доставки к заказу')
                    ->hint('Если выключено, стоимость доставки не рассчитывается и не добавляется к сумме заказа.')
                    ->default(false)
                    ->cast('bool')
                    ->tab('Основное')
                );

                $page->add(Field::make('shipping.kratom.free_enabled', 'checkbox')
                    ->label('Бесплатная доставка')
                    ->hint('Активировать бесплатную доставку от определенной суммы заказа?')
                    ->default(false)
                    ->cast('bool')
                    ->tab('Бесплатная доставка')
                );

                $page->add(Field::make('shipping.kratom.free_min_price', 'number')
                    ->label('Сумма заказа')
                    ->hint('Сумма заказа необходимая для активации бесплатной доставки в валюте storefront.')
                    ->cast('float')
                    ->tab('Бесплатная доставка')
                );

                $page->add(Field::make('shipping.kratom.methods', 'select2_from_array')
                    ->label('Способы доставки')
                    ->options($options)
                    ->allows_multiple(true)
                    ->cast('array')
                    ->hint('Выберите способы доставки доступные на kratom storefront.')
                    ->tab('Основное')
                );
            });
        });
    }
}
