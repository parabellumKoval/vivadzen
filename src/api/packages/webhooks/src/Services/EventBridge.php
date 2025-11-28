<?php

namespace ParabellumKoval\Webhooks\Services;

use Illuminate\Support\Facades\Event;
use ParabellumKoval\Webhooks\Contracts\ResolvesWebhookPayload;
use ParabellumKoval\Webhooks\Services\WebhookDispatcher;
use ParabellumKoval\Webhooks\Services\WebhookRegistry;

class EventBridge
{
    public function __construct(
        protected WebhookRegistry $registry,
        protected WebhookDispatcher $dispatcher
    ) {
    }

    public function register(): void
    {
        $events = $this->registry->events();

        foreach ($events as $eventKey => $config) {
            foreach ($config['sources'] ?? [] as $source) {
                $className = $source['class'] ?? null;
                $resolver = $source['resolver'] ?? null;

                if (!$className || !$resolver) {
                    continue;
                }

                Event::listen($className, function ($event) use ($eventKey, $resolver) {
                    /** @var ResolvesWebhookPayload $resolverInstance */
                    $resolverInstance = app($resolver);
                    $payloads = $resolverInstance->resolve($event);

                    if ($payloads === []) {
                        return;
                    }

                    $this->dispatcher->handleEvent($eventKey, $payloads, ['event' => $eventKey]);
                });
            }
        }
    }
}
