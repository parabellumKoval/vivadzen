<?php

namespace Backpack\Schedule\Traits;

use Backpack\Schedule\Contracts\SchedulableInterface;
use Backpack\Schedule\Models\ScheduledPublication;
use Backpack\Schedule\Observers\SchedulableObserver;
use Carbon\Carbon;

/**
 * Трейт для CRUD контроллеров для добавления полей отложенной публикации
 */
trait HasScheduleFields
{
    /**
     * Добавить поля расписания публикации
     * 
     * @param array $options Опции: tab, wrapper и т.д.
     */
    protected function addScheduleFields(array $options = []): void
    {
        $tab = $options['tab'] ?? 'Таймер публикации';
        $wrapper = $options['wrapper'] ?? ['class' => 'form-group col-sm-12'];

        $entry = $this->crud->getCurrentEntry();
        $model = $this->crud->getModel();

        // Проверяем, поддерживает ли модель отложенную публикацию
        if (!$this->modelSupportsScheduling($model)) {
            return;
        }

        // Получаем настройки из модели
        $overwriteDefault = false;
        if ($entry && method_exists($entry, 'getScheduleOverwriteCreatedAtDefault')) {
            $overwriteDefault = $entry->getScheduleOverwriteCreatedAtDefault();
        } elseif (method_exists($model, 'getScheduleOverwriteCreatedAtDefault')) {
            $overwriteDefault = (new $model)->getScheduleOverwriteCreatedAtDefault();
        }

        // Получаем текущую запланированную публикацию
        $scheduledPublication = null;
        $timeUntilPublish = null;
        
        if ($entry && $entry->exists) {
            $scheduledPublication = ScheduledPublication::where('schedulable_type', get_class($entry))
                ->where('schedulable_id', $entry->getKey())
                ->where('status', 'pending')
                ->first();
            
            if ($scheduledPublication) {
                $timeUntilPublish = $scheduledPublication->time_until_publish;
            }
        }

        // Информационное поле о текущем статусе (только при редактировании)
        if ($entry && $entry->exists && $scheduledPublication) {
            $this->crud->addField([
                'name' => 'schedule_info',
                'type' => 'custom_html',
                'value' => $this->getScheduleInfoHtml($scheduledPublication, $timeUntilPublish),
                'tab' => $tab,
                'wrapper' => $wrapper,
            ]);
        }

        // Чекбокс "Включить отложенную публикацию"
        $this->crud->addField([
            'name' => 'schedule_enabled',
            'label' => 'Включить отложенную публикацию',
            'type' => 'checkbox',
            'default' => $scheduledPublication ? true : false,
            'tab' => $tab,
            'wrapper' => $wrapper,
            'attributes' => [
                'data-schedule-toggle' => 'true',
            ],
        ]);

        // Дата и время публикации
        $this->crud->addField([
            'name' => 'schedule_publish_at',
            'label' => 'Дата и время публикации',
            'type' => 'datetime',
            'default' => $scheduledPublication?->publish_at?->format('Y-m-d H:i:s'),
            'tab' => $tab,
            'wrapper' => array_merge($wrapper, [
                'data-schedule-field' => 'true',
            ]),
            'attributes' => [
                'min' => Carbon::now()->format('Y-m-d\TH:i'),
            ],
        ]);

        // Чекбокс "Перезаписать дату создания"
        $this->crud->addField([
            'name' => 'schedule_overwrite_created_at',
            'label' => 'Перезаписать дату создания',
            'type' => 'checkbox',
            'hint' => 'При публикации поле created_at будет установлено на время публикации',
            'default' => $scheduledPublication?->overwrite_created_at ?? $overwriteDefault,
            'tab' => $tab,
            'wrapper' => array_merge($wrapper, [
                'data-schedule-field' => 'true',
            ]),
        ]);

        // Кнопка отмены публикации (только если есть запланированная)
        if ($scheduledPublication) {
            $this->crud->addField([
                'name' => 'schedule_cancel',
                'type' => 'custom_html',
                'value' => '<button type="button" class="btn btn-outline-danger btn-sm" id="cancel-scheduled-publication" 
                    data-publication-id="' . $scheduledPublication->id . '">
                    <i class="la la-times"></i> Отменить запланированную публикацию
                </button>',
                'tab' => $tab,
                'wrapper' => $wrapper,
            ]);
        }
    }

    /**
     * Получить HTML для информационного поля
     */
    protected function getScheduleInfoHtml(?ScheduledPublication $publication, ?string $timeUntilPublish): string
    {
        if (!$publication) {
            return '';
        }

        $publishAt = $publication->publish_at->format('d.m.Y H:i');
        $statusClass = 'info';
        $icon = 'la-clock';

        if ($publication->publish_at->isPast()) {
            $statusClass = 'warning';
            $timeUntilPublish = 'Ожидает обработки';
            $icon = 'la-hourglass-half';
        }

        return <<<HTML
        <div class="alert alert-{$statusClass} d-flex align-items-center" role="alert">
            <i class="la {$icon} mr-2" style="font-size: 24px;"></i>
            <div>
                <strong>Запланированная публикация</strong><br>
                <span>Дата: {$publishAt}</span><br>
                <span>{$timeUntilPublish}</span>
            </div>
        </div>
        HTML;
    }

    /**
     * Проверить, поддерживает ли модель отложенную публикацию
     */
    protected function modelSupportsScheduling($model): bool
    {
        if (is_string($model)) {
            $model = new $model;
        }

        // Проверяем интерфейс
        if ($model instanceof SchedulableInterface) {
            return true;
        }

        // Проверяем наличие трейта через методы
        if (method_exists($model, 'getSchedulePublishField') && 
            method_exists($model, 'schedulePublication')) {
            return true;
        }

        return false;
    }

    /**
     * Обработать данные расписания после сохранения
     * Вызывать в store() и update() методах контроллера
     */
    protected function processScheduleData($entry): void
    {
        // Помечаем модель как обработанную, чтобы observer не дублировал логику
        SchedulableObserver::markAsProcessed($entry);

        $request = $this->crud->getRequest();

        $scheduleEnabled = $request->boolean('schedule_enabled');
        $publishAt = $request->input('schedule_publish_at');
        $overwriteCreatedAt = $request->boolean('schedule_overwrite_created_at');

        if (!$scheduleEnabled || !$publishAt) {
            // Отменяем существующие публикации если расписание отключено
            if (method_exists($entry, 'cancelScheduledPublications')) {
                $entry->cancelScheduledPublications();
            }
            return;
        }

        $publishAtCarbon = Carbon::parse($publishAt);

        // Если дата в прошлом, не планируем
        if ($publishAtCarbon->isPast()) {
            return;
        }

        // Планируем публикацию
        if (method_exists($entry, 'schedulePublication')) {
            $entry->schedulePublication($publishAtCarbon, $overwriteCreatedAt);
        }
    }
}
