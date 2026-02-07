<?php

namespace Backpack\Schedule;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Backpack\Schedule\Console\Commands\ProcessScheduledPublications;
use Backpack\Schedule\Settings\ScheduleSettingsRegistrar;
use Backpack\Schedule\Services\ScheduleService;

class ScheduleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        // Регистрируем команды
        if ($this->app->runningInConsole()) {
            $this->commands([
                ProcessScheduledPublications::class,
            ]);
        }

        // Миграции
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Маршруты
        $this->loadRoutesFrom(__DIR__ . '/routes/backpack/routes.php');

        // Views
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'schedule');

        // Публикация конфига
        $this->publishes([
            __DIR__ . '/config/schedule.php' => config_path('backpack/schedule.php'),
        ], 'config');

        // Публикация миграций
        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
        ], 'migrations');

        // Публикация views
        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor/schedule'),
        ], 'views');

        // Регистрируем планировщик
        $this->app->booted(function () {
            $this->registerScheduler();
        });

        // Регистрируем настройки в Settings пакете
        $this->registerSettings();
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Мержим конфиг
        $this->mergeConfigFrom(__DIR__ . '/config/schedule.php', 'backpack.schedule');

        // Регистрируем сервис
        $this->app->singleton(ScheduleService::class, function () {
            return new ScheduleService();
        });

        // Алиас для фасада
        $this->app->alias(ScheduleService::class, 'schedule.service');
    }

    /**
     * Регистрируем задачу в планировщике Laravel
     */
    protected function registerScheduler(): void
    {
        $schedule = $this->app->make(Schedule::class);
        
        // Получаем интервал из настроек
        $interval = \Settings::get('backpack.schedule.check_interval', 5);
        $batchSize = \Settings::get('backpack.schedule.batch_size', 100);

        // Регистрируем команду
        $schedule->command("schedule:publish --limit={$batchSize}")
            ->everyMinute()
            ->when(function () use ($interval) {
                // Запускаем только каждые N минут
                return now()->minute % $interval === 0;
            })
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Регистрируем настройки в пакете Settings
     */
    protected function registerSettings(): void
    {
        // Проверяем, что пакет Settings доступен
        if (!class_exists(\Backpack\Settings\Services\Registry\Registry::class)) {
            return;
        }

        // Регистрируем через событие или сервис
        $this->app->booted(function () {
            if (app()->bound('settings.registry')) {
                $registry = app('settings.registry');
                $registrar = new ScheduleSettingsRegistrar();
                $registrar->register($registry);
            }
        });
    }
}
