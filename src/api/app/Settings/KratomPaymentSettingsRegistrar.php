<?php

namespace App\Settings;

use Backpack\Settings\Contracts\SettingsRegistrarInterface;
use Backpack\Settings\Services\Registry\Field;
use Backpack\Settings\Services\Registry\Registry;
use Backpack\Store\app\Support\CheckoutMethodCatalog;

class KratomPaymentSettingsRegistrar implements SettingsRegistrarInterface
{
    public function register(Registry $registry): void
    {
        $registry->group('payment', function ($group) {
            $group->page('Кратом', function ($page) {
                $payments = CheckoutMethodCatalog::paymentMethods();

                $options = array_reduce($payments, function ($carry, $item) {
                    $key = $item['name'] . '_' . $item['type'];
                    $carry[$key] = $item['label'];
                    return $carry;
                }, []);

                $page->add(Field::make('payment.kratom.methods', 'select2_from_array')
                    ->label('Способы оплаты')
                    ->options($options)
                    ->allows_multiple(true)
                    ->cast('array')
                    ->hint('Выберите способы оплаты доступные на kratom storefront.')
                );
            });
        });
    }
}
