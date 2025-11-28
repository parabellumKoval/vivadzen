<?php

namespace ParabellumKoval\Webhooks\Resolvers;

use Backpack\Store\app\Events\CurrencyRateChanged;
use ParabellumKoval\Webhooks\Contracts\ResolvesWebhookPayload;

class CurrencyRateChangedResolver implements ResolvesWebhookPayload
{
    public function resolve(object $event): array
    {
        if (!$event instanceof CurrencyRateChanged) {
            return [];
        }

        return [[
            'id' => $event->rateId,
            'code' => $event->currencyCode,
            'fetched_at' => optional($event->fetchedAt)->timestamp,
        ]];
    }
}
