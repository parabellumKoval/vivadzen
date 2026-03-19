<?php

namespace Backpack\Schedule\Observers;

use Backpack\Schedule\Contracts\SchedulableInterface;
use Backpack\Schedule\Models\ScheduledPublication;
use Backpack\Schedule\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SchedulableObserver
{
    protected ScheduleService $scheduleService;

    /**
     * Флаг для предотвращения повторной обработки
     * Ключ: "class:id", значение: true
     */
    protected static array $processed = [];

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Пометить модель как уже обработанную (вызывается из HasScheduleFields)
     */
    public static function markAsProcessed(Model $model): void
    {
        $key = get_class($model) . ':' . $model->getKey();
        static::$processed[$key] = true;
    }

    /**
     * Проверить, была ли модель уже обработана
     */
    public static function wasProcessed(Model $model): bool
    {
        $key = get_class($model) . ':' . $model->getKey();
        return isset(static::$processed[$key]);
    }

    /**
     * Очистить флаг обработки (вызывается после завершения запроса)
     */
    public static function clearProcessed(): void
    {
        static::$processed = [];
    }

    /**
     * Handle the Model "saved" event.
     * Обрабатывает данные расписания из запроса
     */
    public function saved(Model $model): void
    {
        // Пропускаем если уже обработано через HasScheduleFields::processScheduleData()
        if (static::wasProcessed($model)) {
            return;
        }

        $request = request();
        
        // Проверяем, есть ли данные расписания в запросе
        if (!$request->has('schedule_enabled')) {
            return;
        }

        $scheduleEnabled = $request->boolean('schedule_enabled');
        $publishAt = $request->input('schedule_publish_at');
        $overwriteCreatedAt = $request->boolean('schedule_overwrite_created_at');

        if (!$scheduleEnabled || !$publishAt) {
            // Отменяем существующие публикации если расписание отключено
            $this->scheduleService->cancel($model);
            return;
        }

        try {
            $publishAtCarbon = Carbon::parse($publishAt);

            // Если дата в прошлом, не планируем
            if ($publishAtCarbon->isPast()) {
                return;
            }

            // Планируем публикацию
            $this->scheduleService->schedule($model, $publishAtCarbon, $overwriteCreatedAt);
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем сохранение
            \Log::warning('Failed to schedule publication: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Model "deleted" event.
     * Отменяем все запланированные публикации при удалении модели
     */
    public function deleted(Model $model): void
    {
        ScheduledPublication::where('schedulable_type', $model->getMorphClass())
            ->where('schedulable_id', $model->getKey())
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);
    }
}
