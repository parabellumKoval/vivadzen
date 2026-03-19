<?php

namespace Backpack\Schedule\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Backpack\Schedule\Events\ScheduledPublicationExecuted;
use Backpack\Schedule\Events\ScheduledPublicationFailed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

class ScheduledPublication extends Model
{
    use CrudTrait;

    protected $table = 'ak_scheduled_publications';

    protected $fillable = [
        'schedulable_type',
        'schedulable_id',
        'publish_at',
        'overwrite_created_at',
        'publish_field',
        'publish_value',
        'status',
        'published_at',
        'metadata',
    ];

    protected $casts = [
        'publish_at' => 'datetime',
        'published_at' => 'datetime',
        'overwrite_created_at' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Полиморфная связь с моделью
     */
    public function schedulable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope для получения ожидающих публикаций
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope для получения публикаций, готовых к выполнению
     */
    public function scopeReadyToPublish($query)
    {
        return $query->pending()
            ->where('publish_at', '<=', Carbon::now());
    }

    /**
     * Scope для фильтрации по типу модели
     */
    public function scopeForModel($query, string $modelClass)
    {
        if (class_exists($modelClass)) {
            $modelClass = (new $modelClass())->getMorphClass();
        }

        return $query->where('schedulable_type', $modelClass);
    }

    /**
     * Выполнить публикацию
     */
    public function publish(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $schedulable = $this->schedulable;

        if (!$schedulable) {
            $this->update(['status' => 'cancelled']);
            return false;
        }

        // Устанавливаем значение поля публикации
        $field = $this->publish_field;
        $value = $this->publish_value;
        
        // Преобразуем значение в правильный тип
        if ($value === '1' || $value === 'true') {
            $value = true;
        } elseif ($value === '0' || $value === 'false') {
            $value = false;
        }

        $schedulable->{$field} = $value;

        // Перезаписываем created_at если нужно
        if ($this->overwrite_created_at) {
            $schedulable->created_at = $this->publish_at;
        }

        $schedulable->save();

        // Обновляем статус
        $this->update([
            'status' => 'published',
            'published_at' => Carbon::now(),
        ]);

        // Отправляем событие
        event(new ScheduledPublicationExecuted($this));

        return true;
    }

    /**
     * Отменить публикацию
     */
    public function cancel(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->update(['status' => 'cancelled']);
        return true;
    }

    /**
     * Получить время до публикации
     */
    public function getTimeUntilPublishAttribute(): ?string
    {
        if ($this->status !== 'pending') {
            return null;
        }

        $now = Carbon::now();
        $publishAt = $this->publish_at;

        if ($publishAt->isPast()) {
            return 'Ожидает обработки';
        }

        return $publishAt->diffForHumans($now, [
            'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
            'parts' => 2,
        ]);
    }

    /**
     * Получить человекочитаемое название модели
     */
    public function getModelNameAttribute(): string
    {
        $type = $this->schedulable_type;
        
        // Пытаемся получить название из настроек
        $modelsList = \Settings::get('backpack.schedule.models_list', []);
        
        foreach ($modelsList as $item) {
            if (($item['model'] ?? null) === $type) {
                return $item['name'] ?? class_basename($type);
            }
        }
        
        return class_basename($type);
    }

    /**
     * Получить ссылку на редактирование модели
     */
    public function getEditUrlAttribute(): ?string
    {
        $schedulable = $this->schedulable;
        
        if (!$schedulable) {
            return null;
        }

        // 1. Проверяем, реализует ли модель HasCrudCardInterface
        if ($schedulable instanceof \Backpack\Schedule\Contracts\HasCrudCardInterface) {
            return $schedulable->getCrudEditUrl();
        }

        // 2. Пытаемся получить URL из настроек моделей
        $modelsList = \Settings::get('backpack.schedule.models_list', []);
        
        foreach ($modelsList as $item) {
            if (($item['model'] ?? null) === $this->schedulable_type) {
                $route = $item['route'] ?? null;
                if ($route) {
                    return url(config('backpack.base.route_prefix') . '/' . $route . '/' . $this->schedulable_id . '/edit');
                }
            }
        }

        // 3. Пробуем конфигурацию schedulable_cards_config
        $cardsConfig = config('backpack.schedule.schedulable_cards_config', []);
        if (isset($cardsConfig[$this->schedulable_type]['edit_route'])) {
            return backpack_url($cardsConfig[$this->schedulable_type]['edit_route'] . '/' . $this->schedulable_id . '/edit');
        }

        return null;
    }

    /**
     * Получить статус для отображения
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => '<span class="badge badge-warning">Ожидает</span>',
            'published' => '<span class="badge badge-success">Опубликовано</span>',
            'cancelled' => '<span class="badge badge-secondary">Отменено</span>',
            default => '<span class="badge badge-dark">Неизвестно</span>',
        };
    }

    /**
     * Кнопка отмены публикации
     */
    public function getCancelButton(): string
    {
        if ($this->status !== 'pending') {
            return '';
        }

        $url = route('scheduled-publication.cancel', $this->id);
        
        return <<<HTML
        <a href="javascript:void(0)" 
           onclick="cancelPublication({$this->id})" 
           class="btn btn-sm btn-link text-danger" 
           title="Отменить публикацию">
            <i class="la la-times"></i>
        </a>
        <script>
        function cancelPublication(id) {
            if (!confirm('Отменить запланированную публикацию?')) return;
            fetch('{$url}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    new Noty({type: 'success', text: data.message}).show();
                    crud.table.ajax.reload();
                } else {
                    new Noty({type: 'error', text: data.message}).show();
                }
            });
        }
        </script>
        HTML;
    }

    /**
     * Кнопка "Опубликовать сейчас"
     */
    public function getPublishNowButton(): string
    {
        if ($this->status !== 'pending') {
            return '';
        }

        $url = route('scheduled-publication.publish-now', $this->id);
        
        return <<<HTML
        <a href="javascript:void(0)" 
           onclick="publishNow{$this->id}()" 
           class="btn btn-sm btn-link text-success" 
           title="Опубликовать сейчас">
            <i class="la la-check"></i>
        </a>
        <script>
        function publishNow{$this->id}() {
            if (!confirm('Опубликовать запись сейчас?')) return;
            fetch('{$url}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    new Noty({type: 'success', text: data.message}).show();
                    crud.table.ajax.reload();
                } else {
                    new Noty({type: 'error', text: data.message}).show();
                }
            });
        }
        </script>
        HTML;
    }

    /**
     * Кнопка перехода к настройкам в списке CRUD
     */
    public static function getSettingsButtonHtml(): string
    {
        $url = url(config('backpack.base.route_prefix') . '/setting/schedule');
        
        return <<<HTML
        <a href="{$url}" class="btn btn-outline-info btn-sm">
            <i class="la la-cog"></i> Настройки планировщика
        </a>
        HTML;
    }
}
