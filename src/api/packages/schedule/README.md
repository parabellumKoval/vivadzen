# Backpack Schedule

Пакет для отложенной публикации записей в Laravel Backpack.

## Установка

```bash
composer require parabellumkoval/backpack-schedule
```

## Миграции

```bash
php artisan migrate
```

## Использование

### 1. Добавьте трейт и интерфейс к вашей модели

```php
use Backpack\Schedule\Contracts\SchedulableInterface;
use Backpack\Schedule\Traits\Schedulable;

class Review extends Model implements SchedulableInterface
{
    use Schedulable;
    
    // Поле, которое будет переключаться при публикации
    public function getSchedulePublishField(): string
    {
        return 'is_moderated';
    }
    
    // Значение по умолчанию для "перезаписать дату создания"
    public function getScheduleOverwriteCreatedAtDefault(): bool
    {
        return true;
    }
}
```

### 2. Добавьте поля расписания в CRUD контроллер

```php
use Backpack\Schedule\Traits\HasScheduleFields;

class ReviewCrudController extends CrudController
{
    use HasScheduleFields;
    
    protected function setupCreateOperation()
    {
        // ... ваши поля
        
        $this->addScheduleFields(['tab' => 'Таймер']);
    }
    
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
```

### 3. Настройте планировщик

В `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $interval = \Settings::get('backpack.schedule.check_interval', 5);
    $schedule->command('schedule:publish')->everyMinutes($interval);
}
```

## Настройки

Настройки доступны в админ-панели в разделе "Планировщик публикаций".

## Лицензия

MIT
