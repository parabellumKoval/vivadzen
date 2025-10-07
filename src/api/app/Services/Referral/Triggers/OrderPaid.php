<?php

namespace App\Services\Referral\Triggers;

use Backpack\Profile\app\Contracts\ReferralTrigger;

class OrderPaid implements ReferralTrigger
{
    public static function alias(): string { return 'store.order_paid'; }
    public static function label(): string { return 'Оплаченный заказ'; }
    public static function description(): ?string { return 'Начисление за успешную оплату заказа'; }

    public static function capabilities(): array
    {
        return [
            'supports_fixed'    => true,
            'supports_percent'  => true,   // процент «от базы» для самого актёра не актуален
            'supports_levels'   => true,    // можно доп. вознаграждать аплайн
            'supports_actor'    => false,    // платим автору
            'levels_percent_of' => 'actor', // проценты уровней считаем от выплаты актору
            // 'exclusive_by_subject' => true
        ];
    }

    public static function payloadSchema(): array {
        return [
            'order_id' => 'string|int|required',
            'user_id'  => 'int|nullable',
            'total'    => 'numeric|required',
            'currency' => 'string|required', // валюта заказа
        ];
    }

    public function baseAmount(array $payload): ?array
    {
        return ['amount' => (float)$payload['total'], 'currency' => (string)$payload['currency']];
    }
}
