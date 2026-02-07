<?php

namespace Backpack\Schedule\Facades;

use Illuminate\Support\Facades\Facade;
use Backpack\Schedule\Services\ScheduleService;

/**
 * @method static \Backpack\Schedule\Models\ScheduledPublication schedule($model, \Carbon\Carbon $publishAt, bool $overwriteCreatedAt = null)
 * @method static int cancel($model)
 * @method static bool hasScheduled($model)
 * @method static \Backpack\Schedule\Models\ScheduledPublication|null getScheduled($model)
 * @method static int processReady(int $limit = 100)
 * 
 * @see \Backpack\Schedule\Services\ScheduleService
 */
class Schedule extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ScheduleService::class;
    }
}
