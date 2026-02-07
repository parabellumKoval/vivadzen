<?php

namespace Backpack\Schedule\Contracts;

interface SchedulableInterface
{
    /**
     * Получить название поля, которое переключается при публикации
     * Например: 'is_published', 'is_moderated', 'status'
     */
    public function getSchedulePublishField(): string;

    /**
     * Получить значение для установки при публикации
     * По умолчанию true/1
     */
    public function getSchedulePublishValue(): mixed;

    /**
     * Получить значение по умолчанию для опции "перезаписать дату создания"
     */
    public function getScheduleOverwriteCreatedAtDefault(): bool;

    /**
     * Проверить, может ли модель быть опубликована по расписанию
     */
    public function canBeScheduled(): bool;
}
