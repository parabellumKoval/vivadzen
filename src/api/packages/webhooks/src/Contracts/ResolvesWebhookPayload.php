<?php

namespace ParabellumKoval\Webhooks\Contracts;

interface ResolvesWebhookPayload
{
    /**
     * @param object $event
     * @return array<int,array<string,mixed>>
     */
    public function resolve(object $event): array;
}
