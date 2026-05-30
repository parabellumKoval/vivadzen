<?php

namespace Database\Seeders;

use App\Models\DeliveryMethod;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class DeliveryPaymentSeeder extends Seeder
{
    public function run(): void
    {
        $delivery = [
            [
                'code' => 'pickup_praha_zizkov',
                'type' => 'pickup',
                'name' => [
                    'cs' => 'Osobní odběr — Praha Žižkov',
                    'en' => 'Pickup — Prague Žižkov',
                    'ru' => 'Самовывоз — Прага Жижков',
                    'uk' => 'Самовивіз — Прага Жижков',
                ],
                'description' => [
                    'cs' => 'Vyzvedněte v naší pražské provozovně bez čekání.',
                    'en' => 'Collect at our Prague shop, no shipping wait.',
                    'ru' => 'Заберите в нашем пражском пункте без ожидания.',
                    'uk' => 'Заберіть у нашій празькій точці без очікування.',
                ],
                'eta' => [
                    'cs' => 'Dnes po 16:00',
                    'en' => 'Today after 4 pm',
                    'ru' => 'Сегодня после 16:00',
                    'uk' => 'Сьогодні після 16:00',
                ],
                'address' => [
                    'street' => 'Křižíkova 27/6',
                    'city' => 'Praha 8',
                    'zip' => '186 00',
                    'hours' => 'Po–Pá 10:00–18:00',
                ],
                'price' => 0,
                'is_active' => true,
                'position' => 1,
            ],
            [
                'code' => 'pickup_brno_stred',
                'type' => 'pickup',
                'name' => [
                    'cs' => 'Osobní odběr — Brno Střed',
                    'en' => 'Pickup — Brno Centre',
                    'ru' => 'Самовывоз — Брно Центр',
                    'uk' => 'Самовивіз — Брно Центр',
                ],
                'description' => [
                    'cs' => 'Pobočka v centru Brna, hned u zastávky.',
                    'en' => 'Branch in central Brno, next to the tram stop.',
                    'ru' => 'Точка в центре Брно, рядом с остановкой.',
                    'uk' => 'Точка в центрі Брно, біля зупинки.',
                ],
                'eta' => [
                    'cs' => 'Zítra po 12:00',
                    'en' => 'Tomorrow after noon',
                    'ru' => 'Завтра после 12:00',
                    'uk' => 'Завтра після 12:00',
                ],
                'address' => [
                    'street' => 'Masarykova 12',
                    'city' => 'Brno',
                    'zip' => '602 00',
                    'hours' => 'Po–Pá 11:00–19:00, So 10:00–14:00',
                ],
                'price' => 0,
                'is_active' => true,
                'position' => 2,
            ],
            [
                'code' => 'messanger',
                'type' => 'courier',
                'name' => [
                    'cs' => 'Messanger',
                    'en' => 'Messanger',
                    'ru' => 'Messanger',
                    'uk' => 'Messanger',
                ],
                'description' => [
                    'cs' => 'Standardní doručení kurýrem po celé ČR.',
                    'en' => 'Standard courier delivery anywhere in CZ.',
                    'ru' => 'Стандартная курьерская доставка по всей Чехии.',
                    'uk' => 'Стандартна кур’єрська доставка по всій Чехії.',
                ],
                'eta' => [
                    'cs' => '1–2 pracovní dny',
                    'en' => '1–2 business days',
                    'ru' => '1–2 рабочих дня',
                    'uk' => '1–2 робочі дні',
                ],
                'price' => 99,
                'free_above' => 1500,
                'is_active' => true,
                'position' => 3,
            ],
            [
                'code' => 'messanger_express',
                'type' => 'courier',
                'name' => [
                    'cs' => 'Messanger Express',
                    'en' => 'Messanger Express',
                    'ru' => 'Messanger Express',
                    'uk' => 'Messanger Express',
                ],
                'description' => [
                    'cs' => 'Rychlé doručení v Praze a Brně do 3 hodin.',
                    'en' => 'Fast 3-hour delivery in Prague and Brno.',
                    'ru' => 'Быстрая доставка в Праге и Брно за 3 часа.',
                    'uk' => 'Швидка доставка в Празі та Брно за 3 години.',
                ],
                'eta' => [
                    'cs' => 'Do 3 hodin',
                    'en' => 'Within 3 hours',
                    'ru' => 'В течение 3 часов',
                    'uk' => 'Протягом 3 годин',
                ],
                'price' => 290,
                'is_active' => true,
                'position' => 4,
            ],
        ];

        foreach ($delivery as $row) {
            DeliveryMethod::updateOrCreate(['code' => $row['code']], $row);
        }

        $payment = [
            [
                'code' => 'cod_messanger',
                'type' => 'cod',
                'name' => [
                    'cs' => 'Dobírka — Messanger',
                    'en' => 'Cash on delivery — Messanger',
                    'ru' => 'Наложенный платёж — Messanger',
                    'uk' => 'Накладений платіж — Messanger',
                ],
                'description' => [
                    'cs' => 'Zaplaťte kurýrovi při převzetí. Příplatek 30 Kč.',
                    'en' => 'Pay the courier on delivery. 30 CZK fee applies.',
                    'ru' => 'Оплатите курьеру при получении. Доплата 30 Kč.',
                    'uk' => 'Оплата кур’єру при отриманні. Доплата 30 Kč.',
                ],
                'fee' => 30,
                'is_active' => true,
                'position' => 1,
                'delivery_method_codes' => ['messanger'],
            ],
            [
                'code' => 'qr',
                'type' => 'qr',
                'name' => [
                    'cs' => 'Platba QR kódem',
                    'en' => 'QR code payment',
                    'ru' => 'Оплата по QR коду',
                    'uk' => 'Оплата за QR кодом',
                ],
                'description' => [
                    'cs' => 'Naskenujte QR z mobilního bankovnictví. Připíšeme okamžitě.',
                    'en' => 'Scan the QR in your mobile bank app — instant credit.',
                    'ru' => 'Сканируйте QR в мобильном банке — зачисление мгновенно.',
                    'uk' => 'Скануйте QR у мобільному банку — миттєве зарахування.',
                ],
                'fee' => 0,
                'is_active' => true,
                'position' => 2,
            ],
            [
                'code' => 'bank_transfer',
                'type' => 'bank',
                'name' => [
                    'cs' => 'Bankovní převod',
                    'en' => 'Bank transfer',
                    'ru' => 'Банковский перевод',
                    'uk' => 'Банківський переказ',
                ],
                'description' => [
                    'cs' => 'Klasický převod. Po připsání připravíme zásilku.',
                    'en' => 'Classic transfer. We ship as soon as funds arrive.',
                    'ru' => 'Обычный перевод. Отправим после зачисления.',
                    'uk' => 'Звичайний переказ. Відправимо після зарахування.',
                ],
                'fee' => 0,
                'is_active' => true,
                'position' => 3,
            ],
            [
                'code' => 'niftipay',
                'type' => 'online',
                'name' => [
                    'cs' => 'Karta přes niftipay.com',
                    'en' => 'Card via niftipay.com',
                    'ru' => 'Карта через niftipay.com',
                    'uk' => 'Картка через niftipay.com',
                ],
                'description' => [
                    'cs' => 'Platba kartou online. Bezpečné, ihned.',
                    'en' => 'Online card payment. Secure, instant.',
                    'ru' => 'Оплата картой онлайн. Безопасно, мгновенно.',
                    'uk' => 'Оплата карткою онлайн. Безпечно, миттєво.',
                ],
                'fee' => 0,
                'is_active' => true,
                'position' => 4,
            ],
        ];

        foreach ($payment as $row) {
            PaymentMethod::updateOrCreate(['code' => $row['code']], $row);
        }
    }
}
