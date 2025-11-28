<?php

namespace ParabellumKoval\Webhooks\Support;

use Illuminate\Support\Facades\Cache;

class EventBuffer
{
    public function push(string $unitKey, string $eventKey, array $payloads, array $options = []): array
    {
        $delay = max(1, (int) ($options['delay'] ?? 3));
        $maxItems = (int) ($options['max_items'] ?? 25);
        $ttl = (int) ($options['ttl'] ?? 120);

        $bufferKey = $this->bufferKey($unitKey, $eventKey);
        $lock = Cache::lock($bufferKey . ':lock', 5);
        $bufferPayloads = [];

        $lock->block(2, function () use (&$bufferPayloads, $bufferKey, $payloads, $ttl) {
            $bucket = Cache::get($bufferKey, ['payloads' => []]);
            $bucket['payloads'] = array_merge($bucket['payloads'], $payloads);
            $bucket['updated_at'] = now()->timestamp;
            Cache::put($bufferKey, $bucket, $ttl);
            $bufferPayloads = $bucket['payloads'];
        });

        if (count($bufferPayloads) >= $maxItems) {
            $payloads = $this->pull($unitKey, $eventKey);
            $this->forgetJobReservation($unitKey, $eventKey);

            return [
                'flush_now' => true,
                'payloads' => $payloads,
                'buffer_key' => null,
                'delay' => 0,
                'scheduled' => false,
            ];
        }

        $jobKey = $this->jobKey($unitKey, $eventKey);
        $reserved = Cache::add($jobKey, true, $ttl);

        return [
            'flush_now' => false,
            'payloads' => [],
            'buffer_key' => $bufferKey,
            'delay' => $delay,
            'scheduled' => $reserved,
        ];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function pull(string $unitKey, string $eventKey): array
    {
        $bufferKey = $this->bufferKey($unitKey, $eventKey);
        $lock = Cache::lock($bufferKey . ':lock', 5);
        $payloads = [];

        $lock->block(2, function () use (&$payloads, $bufferKey) {
            $bucket = Cache::pull($bufferKey, ['payloads' => []]);
            $payloads = $bucket['payloads'] ?? [];
        });

        return $payloads;
    }

    public function forgetJobReservation(string $unitKey, string $eventKey): void
    {
        Cache::forget($this->jobKey($unitKey, $eventKey));
    }

    public function bufferKey(string $unitKey, string $eventKey): string
    {
        return 'webhooks:event-buffer:' . $unitKey . ':' . sha1($eventKey);
    }

    protected function jobKey(string $unitKey, string $eventKey): string
    {
        return 'webhooks:event-buffer-job:' . $unitKey . ':' . sha1($eventKey);
    }
}
