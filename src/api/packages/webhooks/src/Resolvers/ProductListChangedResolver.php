<?php

namespace ParabellumKoval\Webhooks\Resolvers;

use Backpack\Store\app\Events\ProductListChanged;
use ParabellumKoval\Webhooks\Contracts\ResolvesWebhookPayload;

class ProductListChangedResolver implements ResolvesWebhookPayload
{
    public function resolve(object $event): array
    {
        if (!$event instanceof ProductListChanged) {
            return [];
        }

        return [[
            'id' => $event->listId,
            'slug' => $event->slug,
            'action' => $event->action,
        ]];
    }
}
