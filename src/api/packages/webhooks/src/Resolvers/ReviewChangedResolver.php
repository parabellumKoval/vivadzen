<?php

namespace ParabellumKoval\Webhooks\Resolvers;

use Backpack\Reviews\app\Events\ReviewChanged;
use ParabellumKoval\Webhooks\Contracts\ResolvesWebhookPayload;

class ReviewChangedResolver implements ResolvesWebhookPayload
{
    public function resolve(object $event): array
    {
        if (!$event instanceof ReviewChanged) {
            return [];
        }

        return [[
            'id' => $event->reviewId,
            'is_video' => $event->isVideo,
            'action' => $event->action,
        ]];
    }
}
