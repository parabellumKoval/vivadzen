<?php

namespace ParabellumKoval\Webhooks\Resolvers;

use Backpack\Articles\app\Events\ArticleChanged;
use ParabellumKoval\Webhooks\Contracts\ResolvesWebhookPayload;

class ArticleChangedResolver implements ResolvesWebhookPayload
{
    public function resolve(object $event): array
    {
        if (!$event instanceof ArticleChanged) {
            return [];
        }

        return [[
            'id' => $event->articleId,
            'slug' => $event->slug,
            'action' => $event->action,
        ]];
    }
}
