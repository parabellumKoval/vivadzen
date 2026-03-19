<?php

namespace Tests\Feature\Dumper;

use Illuminate\Console\Scheduling\Schedule;
use Mockery;
use ParabellumKoval\Dumper\Services\AutoDumpScheduler;
use ParabellumKoval\Dumper\Services\DumpManager;
use Tests\TestCase;

class AutoDumpSchedulerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_scheduler_registers_auto_dump_case_as_positional_argument(): void
    {
        $manager = Mockery::mock(DumpManager::class);
        $manager->shouldReceive('autoCases')->once()->andReturn([
            'kazdye_30_minut' => [
                'key' => 'kazdye_30_minut',
                'label' => 'Каждые 30 минут',
                'cron' => '*/30 * * * *',
                'schedule' => 'every_30_minutes',
            ],
        ]);

        $schedule = app(Schedule::class);
        $existingEventsCount = count($schedule->events());

        (new AutoDumpScheduler($manager, $schedule))->register();

        $registeredEvents = array_slice($schedule->events(), $existingEventsCount);
        $event = collect($registeredEvents)
            ->first(fn ($registeredEvent) => str_contains((string) $registeredEvent->command, 'dumper:auto'));

        $this->assertNotNull($event);
        $this->assertStringContainsString("dumper:auto 'kazdye_30_minut'", $event->command);
        $this->assertStringNotContainsString("case='kazdye_30_minut'", $event->command);
    }
}
