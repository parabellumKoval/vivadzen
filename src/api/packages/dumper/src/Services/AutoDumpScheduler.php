<?php

namespace ParabellumKoval\Dumper\Services;

use Illuminate\Console\Scheduling\Schedule;

class AutoDumpScheduler
{
    public function __construct(
        protected DumpManager $manager,
        protected Schedule $schedule
    ) {
    }

    public function register(): void
    {
        $cases = $this->manager->autoCases();

        foreach ($cases as $case) {
            if (empty($case['cron'])) {
                continue;
            }

            $name = 'dumper.auto.' . $case['key'];
            $description = $case['description'] ?? 'Auto dump case: ' . $case['label'];

            $this->schedule->command('dumper:auto', ['case' => $case['key']])
                ->cron($case['cron'])
                ->withoutOverlapping()
                ->name($name)
                ->description($description);
        }
    }
}
