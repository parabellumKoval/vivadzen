<?php

namespace Backpack\Schedule\Traits;

use Backpack\Schedule\Models\ScheduledPublication;
use Backpack\Schedule\Observers\SchedulableObserver;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Carbon\Carbon;

/**
 * Трейт для моделей, поддерживающих отложенную публикацию
 * 
 * @property-read ScheduledPublication|null $scheduledPublication
 * @property-read \Illuminate\Database\Eloquent\Collection|ScheduledPublication[] $scheduledPublications
 */
trait Schedulable
{
    /**
     * Boot the trait
     */
    public static function bootSchedulable(): void
    {
        static::observe(SchedulableObserver::class);
    }

    /**
     * Получить текущую запланированную публикацию (pending)
     */
    public function scheduledPublication(): MorphOne
    {
        return $this->morphOne(ScheduledPublication::class, 'schedulable')
            ->where('status', 'pending')
            ->latest('publish_at');
    }

    /**
     * Получить все запланированные публикации
     */
    public function scheduledPublications(): MorphMany
    {
        return $this->morphMany(ScheduledPublication::class, 'schedulable');
    }

    /**
     * Запланировать публикацию
     * Обновляет существующую запись или создает новую
     */
    public function schedulePublication(
        Carbon $publishAt,
        bool $overwriteCreatedAt = null,
        ?string $publishField = null,
        mixed $publishValue = null
    ): ScheduledPublication {
        // Используем значения по умолчанию из модели если не указаны
        $overwriteCreatedAt = $overwriteCreatedAt ?? $this->getScheduleOverwriteCreatedAtDefault();
        $publishField = $publishField ?? $this->getSchedulePublishField();
        $publishValue = $publishValue ?? $this->getSchedulePublishValue();

        $publishValueString = is_bool($publishValue) ? ($publishValue ? '1' : '0') : (string) $publishValue;

        // Ищем существующую pending запись
        $existing = $this->scheduledPublication;

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
        return $this->scheduledPublications()->create([
            'publish_at' => $publishAt,
            'overwrite_created_at' => $overwriteCreatedAt,
            'publish_field' => $publishField,
            'publish_value' => $publishValueString,
            'status' => 'pending',
        ]);
    }

    /**
     * Отменить все ожидающие публикации
     */
    public function cancelScheduledPublications(): int
    {
        return $this->scheduledPublications()
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);
    }

    /**
     * Проверить, есть ли запланированная публикация
     */
    public function hasScheduledPublication(): bool
    {
        return $this->scheduledPublication()->exists();
    }

    /**
     * Получить время до публикации
     */
    public function getTimeUntilPublish(): ?string
    {
        $scheduled = $this->scheduledPublication;
        
        if (!$scheduled) {
            return null;
        }

        return $scheduled->time_until_publish;
    }

    // ========================================
    // Методы для переопределения в моделях
    // ========================================

    /**
     * Получить название поля, которое переключается при публикации
     */
    public function getSchedulePublishField(): string
    {
        return property_exists($this, 'schedulePublishField') 
            ? $this->schedulePublishField 
            : 'is_published';
    }

    /**
     * Получить значение для установки при публикации
     */
    public function getSchedulePublishValue(): mixed
    {
        return property_exists($this, 'schedulePublishValue') 
            ? $this->schedulePublishValue 
            : true;
    }

    /**
     * Получить значение по умолчанию для опции "перезаписать дату создания"
     */
    public function getScheduleOverwriteCreatedAtDefault(): bool
    {
        return property_exists($this, 'scheduleOverwriteCreatedAt') 
            ? $this->scheduleOverwriteCreatedAt 
            : false;
    }

    /**
     * Проверить, может ли модель быть опубликована по расписанию
     */
    public function canBeScheduled(): bool
    {
        return property_exists($this, 'canBeScheduled') 
            ? $this->canBeScheduled 
            : true;
    }
}
