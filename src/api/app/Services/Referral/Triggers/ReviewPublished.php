<?php

namespace App\Services\Referral\Triggers;

use Backpack\Profile\app\Contracts\ReferralTrigger;

class ReviewPublished implements ReferralTrigger
{
    public static function alias(): string { return 'review.published'; }
    public static function label(): string { return 'Опубликованный отзыв'; }
    public static function description(): ?string { return 'Фикс-начисление за отзыв'; }

    public static function capabilities(): array
    {
        return [
            'supports_fixed'    => true,
            'supports_percent'  => false,   // процент «от базы» для самого актёра не актуален
            'supports_levels'   => false,    // можно доп. вознаграждать аплайн
            'supports_actor'    => true,    // платим автору
            'levels_percent_of' => 'actor', // проценты уровней считаем от выплаты актору
            'exclusive_by_subject' => true
        ];
    }

    public static function payloadSchema(): array {
        return [
            'review_id' => 'int|required',
            'user_id'   => 'int|nullable',
            'rating'    => 'numeric|nullable',
        ];
    }

    public function baseAmount(array $payload): ?array
    {
        // для фикс-начислений базовая сумма может быть 1 (или null — неважно)
        // return ['amount' => 1.0, 'currency' => \Settings::get('profile.points.code', 'VIVAPOINTS')];
        return null;
    }
}
