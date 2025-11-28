<?php

namespace ParabellumKoval\Webhooks\Resolvers;

use Backpack\Settings\Events\SettingsGroupChanged;
use ParabellumKoval\Webhooks\Contracts\ResolvesWebhookPayload;

class SettingsGroupChangedResolver implements ResolvesWebhookPayload
{
    public function resolve(object $event): array
    {
        if (!$event instanceof SettingsGroupChanged) {
            return [];
        }

        return [[
            'group' => $event->group,
            'diff' => $event->diff,
        ]];
    }
}
