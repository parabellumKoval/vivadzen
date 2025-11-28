<?php

namespace ParabellumKoval\Webhooks\Resolvers;

use Backpack\Store\app\Events\CategoryChanged;
use ParabellumKoval\Webhooks\Contracts\ResolvesWebhookPayload;

class CategoryChangedResolver implements ResolvesWebhookPayload
{
    public function resolve(object $event): array
    {
        if (!$event instanceof CategoryChanged) {
            return [];
        }

        return [[
            'id' => $event->categoryId,
            'slug' => $event->slug,
            'action' => $event->action,
        ]];
    }
}
