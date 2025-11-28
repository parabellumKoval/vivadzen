<?php

namespace ParabellumKoval\Webhooks\Services;

use Illuminate\Console\Scheduling\Schedule;

class ScheduleBridge
{
    public function __construct(
        protected WebhookRegistry $registry,
        protected WebhookDispatcher $dispatcher,
        protected Schedule $schedule
    ) {
    }

    public function register(): void
    {
        $schedules = $this->registry->schedules();

        foreach ($schedules as $key => $definition) {
            $units = $definition['units'] ?? [];
            if ($units === []) {
                continue;
            }

            $cron = $definition['cron'] ?? null;
            if (!$cron) {
                continue;
            }

            $description = $definition['description'] ?? 'Webhooks schedule: ' . $key;

            $this->schedule->call(function () use ($key, $units) {
                $this->dispatcher->dispatchScheduled($key, $units);
            })
                ->cron($cron)
                ->name('webhooks:' . $key)
                ->withoutOverlapping()
                ->description($description);
        }
    }
}
