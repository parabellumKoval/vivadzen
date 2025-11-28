<?php

namespace App\Services\Referral\Triggers;

use Backpack\Profile\app\Contracts\ReferralTrigger;

class ReviewTextPublished implements ReferralTrigger
{
    public static function alias(): string { return 'review.published.text'; }
    public static function label(): string { return 'Опубликованный текстовый отзыв'; }
    public static function description(): ?string { return 'Фикс-начисление за текстовый отзыв'; }

    public static function capabilities(): array
    {
        return [
            'supports_fixed'    => true,
            'supports_percent'  => false,
            'supports_levels'   => false,
            'supports_actor'    => true,
            'levels_percent_of' => 'actor',
            'exclusive_by_subject' => true,
        ];
    }

    public static function payloadSchema(): array
    {
        return [
            'review_id' => 'int|required',
            'user_id'   => 'int|nullable',
            'rating'    => 'numeric|nullable',
        ];
    }

    public function baseAmount(array $payload): ?array
    {
        return null;
    }
}
