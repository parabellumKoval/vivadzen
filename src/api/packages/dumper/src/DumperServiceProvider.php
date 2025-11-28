<?php

namespace ParabellumKoval\Dumper;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;
use ParabellumKoval\Dumper\Console\Commands\RunAutoDumpCommand;
use ParabellumKoval\Dumper\Services\AutoDumpScheduler;
use ParabellumKoval\Dumper\Services\DumpManager;
use ParabellumKoval\Dumper\Services\TableInspector;

class DumperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/dumper.php', 'dumper');

        $this->app->singleton(TableInspector::class, function ($app) {
            return new TableInspector(
                $app->make(DatabaseManager::class),
                $app->make(Repository::class)
            );
        });

        $this->app->singleton(DumpManager::class, function ($app) {
            return new DumpManager(
                $app->make(FilesystemFactory::class),
                $app->make(DatabaseManager::class),
                $app->make(Repository::class),
                $app->make(TableInspector::class)
            );
        });

        $this->app->singleton(AutoDumpScheduler::class, function ($app) {
            return new AutoDumpScheduler(
                $app->make(DumpManager::class),
                $app->make(\Illuminate\Console\Scheduling\Schedule::class)
            );
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                RunAutoDumpCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'dumper');
        $this->loadRoutesFrom(__DIR__ . '/routes/backpack.php');
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'dumper');

        $this->publishes([
            __DIR__ . '/config/dumper.php' => config_path('dumper.php'),
        ], 'dumper-config');

        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor/dumper'),
        ], 'dumper-views');

        $this->publishes([
            __DIR__ . '/resources/lang' => resource_path('lang/vendor/dumper'),
        ], 'dumper-lang');

        $this->app->booted(function () {
            if ($this->app->runningInConsole()) {
                app(AutoDumpScheduler::class)->register();
            }
        });
    }
}
