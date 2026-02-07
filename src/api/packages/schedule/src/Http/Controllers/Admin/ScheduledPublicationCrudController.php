<?php

namespace Backpack\Schedule\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\Schedule\Models\ScheduledPublication;
use Illuminate\Http\Request;

class ScheduledPublicationCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\BulkDeleteOperation;

    public function setup()
    {
        CRUD::setModel(ScheduledPublication::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/scheduled-publication');
        CRUD::setEntityNameStrings('публикация', 'запланированные публикации');
    }

    protected function setupListOperation()
    {
        // Фильтры
        $this->setupFilters();

        // Колонки
        CRUD::addColumn([
            'name' => 'id',
            'label' => 'ID',
            'type' => 'number',
        ]);

        CRUD::addColumn([
            'name' => 'model_name',
            'label' => 'Тип модели',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'schedulable_id',
            'label' => 'ID записи',
            'type' => 'number',
        ]);

        // Связанная запись - используем кастомный view с поддержкой HasCrudCardInterface
        CRUD::addColumn([
            'name' => 'schedulable',
            'label' => 'Запись',
            'type' => 'view',
            'view' => 'schedule::columns.schedulable',
            'escaped' => false,
        ]);

        CRUD::addColumn([
            'name' => 'publish_at',
            'label' => 'Дата публикации',
            'type' => 'datetime',
        ]);

        CRUD::addColumn([
            'name' => 'time_until_publish',
            'label' => 'До публикации',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'overwrite_created_at',
            'label' => 'Перезапись даты',
            'type' => 'boolean',
        ]);

        CRUD::addColumn([
            'name' => 'publish_field',
            'label' => 'Поле',
            'type' => 'text',
        ]);

        CRUD::addColumn([
            'name' => 'status_label',
            'label' => 'Статус',
            'type' => 'custom_html',
            'escaped' => false,
        ]);

        CRUD::addColumn([
            'name' => 'published_at',
            'label' => 'Опубликовано',
            'type' => 'datetime',
        ]);

        CRUD::addColumn([
            'name' => 'created_at',
            'label' => 'Создано',
            'type' => 'datetime',
        ]);

        // Кнопки
        $this->addCustomButtons();

        // Сортировка по умолчанию
        CRUD::orderBy('publish_at', 'asc');
    }

    protected function setupShowOperation()
    {
        $this->setupListOperation();
    }

    protected function setupFilters()
    {
        // Фильтр по статусу
        CRUD::addFilter([
            'name' => 'status',
            'type' => 'dropdown',
            'label' => 'Статус',
        ], [
            'pending' => 'Ожидает',
            'published' => 'Опубликовано',
            'cancelled' => 'Отменено',
        ], function ($value) {
            CRUD::addClause('where', 'status', $value);
        });

        // Фильтр по типу модели
        $modelTypes = $this->getModelTypes();
        if (!empty($modelTypes)) {
            CRUD::addFilter([
                'name' => 'schedulable_type',
                'type' => 'dropdown',
                'label' => 'Тип модели',
            ], $modelTypes, function ($value) {
                CRUD::addClause('where', 'schedulable_type', $value);
            });
        }

        // Фильтр по дате
        CRUD::addFilter([
            'name' => 'publish_at',
            'type' => 'date_range',
            'label' => 'Дата публикации',
        ], false, function ($value) {
            $dates = json_decode($value);
            CRUD::addClause('where', 'publish_at', '>=', $dates->from);
            CRUD::addClause('where', 'publish_at', '<=', $dates->to . ' 23:59:59');
        });
    }

    protected function addCustomButtons()
    {
        // Кнопка отмены публикации
        CRUD::addButtonFromModelFunction('line', 'cancel_publication', 'getCancelButton', 'beginning');
        
        // Кнопка "Опубликовать сейчас"
        CRUD::addButtonFromModelFunction('line', 'publish_now', 'getPublishNowButton', 'beginning');
    }

    /**
     * Получить типы моделей для фильтра
     */
    protected function getModelTypes(): array
    {
        $types = ScheduledPublication::select('schedulable_type')
            ->distinct()
            ->pluck('schedulable_type')
            ->toArray();

        $result = [];
        foreach ($types as $type) {
            $result[$type] = class_basename($type);
        }

        return $result;
    }

    /**
     * Получить название записи
     */
    protected function getSchedulableName($entry): string
    {
        $schedulable = $entry->schedulable;
        
        if (!$schedulable) {
            return 'ID: ' . $entry->schedulable_id;
        }

        // Пробуем разные поля для названия
        foreach (['title', 'name', 'text', 'id'] as $field) {
            if (isset($schedulable->{$field})) {
                $value = $schedulable->{$field};
                if (is_string($value) && strlen($value) > 0) {
                    return mb_substr($value, 0, 50) . (mb_strlen($value) > 50 ? '...' : '');
                }
            }
        }

        return 'ID: ' . $entry->schedulable_id;
    }

    /**
     * Отменить публикацию
     */
    public function cancel($id)
    {
        $publication = ScheduledPublication::findOrFail($id);
        
        if ($publication->cancel()) {
            return response()->json([
                'success' => true,
                'message' => 'Публикация отменена',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Не удалось отменить публикацию',
        ], 400);
    }

    /**
     * Опубликовать сейчас
     */
    public function publishNow($id)
    {
        $publication = ScheduledPublication::findOrFail($id);
        
        if ($publication->publish()) {
            return response()->json([
                'success' => true,
                'message' => 'Запись опубликована',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Не удалось опубликовать запись',
        ], 400);
    }

    /**
     * Массовая отмена
     */
    public function bulkCancel(Request $request)
    {
        $ids = $request->input('entries', []);
        
        $cancelled = ScheduledPublication::whereIn('id', $ids)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => "Отменено публикаций: {$cancelled}",
        ]);
    }
}
