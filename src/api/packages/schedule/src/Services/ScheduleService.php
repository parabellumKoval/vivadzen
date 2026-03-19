<?php

namespace Backpack\Schedule\Services;

use Backpack\Schedule\Models\ScheduledPublication;
use Backpack\Schedule\Contracts\SchedulableInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ScheduleService
{
    /**
     * Запланировать публикацию модели
     * Обновляет существующую запись или создает новую
     */
    public function schedule(
        Model $model,
        Carbon $publishAt,
        ?bool $overwriteCreatedAt = null
    ): ScheduledPublication {
        // Проверяем поддержку расписания
        if (!$this->supportsScheduling($model)) {
            throw new \InvalidArgumentException(
                'Model ' . get_class($model) . ' does not support scheduling. ' .
                'Implement SchedulableInterface or use Schedulable trait.'
            );
        }

        // Получаем настройки из модели
        $publishField = method_exists($model, 'getSchedulePublishField')
            ? $model->getSchedulePublishField()
            : 'is_published';

        $publishValue = method_exists($model, 'getSchedulePublishValue')
            ? $model->getSchedulePublishValue()
            : true;

        $overwriteCreatedAt = $overwriteCreatedAt ?? (
            method_exists($model, 'getScheduleOverwriteCreatedAtDefault')
                ? $model->getScheduleOverwriteCreatedAtDefault()
                : \Settings::get('backpack.schedule.default_overwrite_created_at', false)
        );

        $publishValueString = is_bool($publishValue) ? ($publishValue ? '1' : '0') : (string) $publishValue;

        // Ищем существующую pending запись
        $existing = $this->getScheduled($model);

        if ($existing) {
            // Обновляем существующую запись вместо создания новой
            $existing->update([
                'publish_at' => $publishAt,
                'overwrite_created_at' => $overwriteCreatedAt,
                'publish_field' => $publishField,
                'publish_value' => $publishValueString,
            ]);
            return $existing;
        }

        // Создаем новую только если нет существующей
        return ScheduledPublication::create([
            'schedulable_type' => $model->getMorphClass(),
            'schedulable_id' => $model->getKey(),
            'publish_at' => $publishAt,
            'overwrite_created_at' => $overwriteCreatedAt,
            'publish_field' => $publishField,
            'publish_value' => $publishValueString,
            'status' => 'pending',
        ]);
    }

    /**
     * Отменить запланированные публикации для модели
     */
    public function cancel(Model $model): int
    {
        return ScheduledPublication::where('schedulable_type', $model->getMorphClass())
            ->where('schedulable_id', $model->getKey())
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);
    }

    /**
     * Проверить, есть ли запланированная публикация
     */
    public function hasScheduled(Model $model): bool
    {
        return ScheduledPublication::where('schedulable_type', $model->getMorphClass())
            ->where('schedulable_id', $model->getKey())
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Получить запланированную публикацию
     */
    public function getScheduled(Model $model): ?ScheduledPublication
    {
        return ScheduledPublication::where('schedulable_type', $model->getMorphClass())
            ->where('schedulable_id', $model->getKey())
            ->where('status', 'pending')
            ->first();
    }

    /**
     * Обработать готовые к публикации записи
     */
    public function processReady(int $limit = 100): int
    {
        $publications = ScheduledPublication::readyToPublish()
            ->limit($limit)
            ->get();

        $published = 0;

        foreach ($publications as $publication) {
            try {
                if ($publication->publish()) {
                    $published++;
                }
            } catch (\Exception $e) {
                $publication->update([
                    'status' => 'cancelled',
                    'metadata' => array_merge(
                        $publication->metadata ?? [],
                        ['error' => $e->getMessage()]
                    ),
                ]);
            }
        }

        return $published;
    }

    /**
     * Проверить, поддерживает ли модель расписание
     */
    public function supportsScheduling(Model $model): bool
    {
        if ($model instanceof SchedulableInterface) {
            return true;
        }

        // Проверяем наличие методов трейта
        return method_exists($model, 'getSchedulePublishField')
            && method_exists($model, 'scheduledPublication');
    }
}
