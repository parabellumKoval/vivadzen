<?php

namespace ParabellumKoval\Webhooks\Services;

use ParabellumKoval\Webhooks\Jobs\WebhookDispatchJob;
use ParabellumKoval\Webhooks\Support\EventBuffer;

class WebhookDispatcher
{
    public function __construct(
        protected WebhookRegistry $registry,
        protected EventBuffer $buffer
    ) {
    }

    public function dispatchManual(string $unitKey): void
    {
        $this->dispatch($unitKey, 'manual');
    }

    public function dispatch(string $unitKey, string $origin = 'manual', array $payloads = [], ?string $eventKey = null, bool $fromBuffer = false, array $meta = []): void
    {
        $job = new WebhookDispatchJob($unitKey, $payloads, $origin, $eventKey, $meta, $fromBuffer);

        if ($queue = config('webhooks.queue')) {
            $job->onQueue($queue);
        }

        dispatch($job);
    }

    /**
     * @param array<int,array<string,mixed>> $payloads
     */
    public function handleEvent(string $eventKey, array $payloads, array $eventMeta = []): void
    {
        $units = $this->registry->unitsForEvent($eventKey);

        foreach ($units as $unitKey) {
            $unit = $this->registry->find($unitKey);
            if (!$unit) {
                continue;
            }

            $bufferResponse = $this->buffer->push($unitKey, $eventKey, $payloads, $unit['event_buffer']);

            if ($bufferResponse['flush_now']) {
                $this->dispatch($unitKey, 'event', $bufferResponse['payloads'], $eventKey, false, $eventMeta);
                continue;
            }

            if ($bufferResponse['scheduled']) {
                $this->dispatch($unitKey, 'event', [], $eventKey, true, $eventMeta);
            }
        }
    }

    public function dispatchScheduled(string $scheduleKey, array $units): void
    {
        foreach ($units as $unitKey) {
            $this->dispatch($unitKey, 'schedule', [], null, false, ['schedule' => $scheduleKey]);
        }
    }
}
