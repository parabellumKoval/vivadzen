<?php

namespace Tests\Unit;

use Tests\TestCase;

class StorePaymentHelperTest extends TestCase
{
    public function test_store_payment_lines_hide_provider_metadata_for_online_payments(): void
    {
        $lines = store_payment_lines([
            'method' => 'niftipay_online',
            'provider' => 'niftipay',
            'niftipay' => [
                'id' => 'b1624758-0cdf-4fb1-b85d-f8b6ec056c14',
                'qr_url' => 'https://www.niftipay.com/api/qr?data=https%3A%2F%2Fwww.niftipay.com%2Fpaylink%2Fb1624758-0cdf-4fb1-b85d-f8b6ec056c14',
                'status' => 'new',
                'pay_url' => 'https://www.niftipay.com/paylink/b1624758-0cdf-4fb1-b85d-f8b6ec056c14',
                'order_key' => '12562',
                'integration_id' => '580384',
                'updated_at' => '2026-04-07T11:11:57+00:00',
                'reference' => '91c3d2cc-1c33-4594-8aad-8723218aeec5',
            ],
        ]);

        $this->assertSame([
            'Оплата Niftipay онлайн',
        ], $lines);
    }

    public function test_store_payment_lines_keep_only_business_fields_for_manual_payments(): void
    {
        $lines = store_payment_lines([
            'method' => 'bank_transfer',
            'account' => 'UA123456789012345678901234567',
            'invoice_number' => 'INV-2026-0042',
            'comment' => 'Оплата по счёту для юрлица',
            'provider' => 'manual',
            'manual' => [
                'id' => 'c1624758-0cdf-4fb1-b85d-f8b6ec056c14',
                'updated_at' => '2026-04-07T11:11:57+00:00',
            ],
        ]);

        $this->assertSame([
            'Банковский перевод',
            'Счёт: UA123456789012345678901234567',
            'Инвойс: INV-2026-0042',
            'Комментарий: Оплата по счёту для юрлица',
        ], $lines);
    }
}
