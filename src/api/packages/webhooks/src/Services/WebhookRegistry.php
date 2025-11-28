<?php

namespace ParabellumKoval\Webhooks\Services;

class WebhookRegistry
{
    /**
     * @var array<string,array<string,mixed>>
     */
    protected array $units = [];

    /**
     * @var array<string,string[]>
     */
    protected array $eventsToUnits = [];

    /**
     * @var array<string,array<string,mixed>>
     */
    protected array $schedules = [];

    /**
     * @var array<string,array<string,mixed>>
     */
    protected array $events = [];

    public function __construct()
    {
        $defaults = config('webhooks.defaults.event_buffer', []);
        $units = config('webhooks.units', []);

        foreach ($units as $key => $unit) {
            $prepared = $unit;
            $prepared['key'] = (string) $key;
            $prepared['url'] = $unit['url'] ?? [];
            $prepared['urls'] = is_array($prepared['url']) ? $prepared['url'] : [$prepared['url']];
            $prepared['method'] = strtoupper($unit['method'] ?? 'POST');
            $prepared['visible_in_widget'] = $unit['visible_in_widget'] ?? true;
            $prepared['events'] = $unit['events'] ?? [];
            $prepared['schedules'] = $unit['schedules'] ?? [];
            $prepared['event_buffer'] = array_merge($defaults, $unit['event_buffer'] ?? []);
            $prepared['payload'] = $unit['payload'] ?? [];

            $this->units[$prepared['key']] = $prepared;

            foreach ($prepared['events'] as $eventKey) {
                $this->eventsToUnits[$eventKey][] = $prepared['key'];
            }
        }

        $this->schedules = config('webhooks.schedules', []);
        $this->events = config('webhooks.events', []);
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public function all(): array
    {
        return $this->units;
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public function visible(): array
    {
        return array_filter($this->units, fn ($unit) => (bool) ($unit['visible_in_widget'] ?? true));
    }

    public function find(string $key): ?array
    {
        return $this->units[$key] ?? null;
    }

    /**
     * @return string[]
     */
    public function unitsForEvent(string $eventKey): array
    {
        return $this->eventsToUnits[$eventKey] ?? [];
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public function schedules(): array
    {
        return $this->schedules;
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public function events(): array
    {
        return $this->events;
    }
}
