<?php

namespace ParabellumKoval\Webhooks;

use Illuminate\Support\ServiceProvider;
use ParabellumKoval\Webhooks\Services\EventBridge;
use ParabellumKoval\Webhooks\Services\ScheduleBridge;
use ParabellumKoval\Webhooks\Services\WebhookDispatcher;
use ParabellumKoval\Webhooks\Services\WebhookRegistry;
use ParabellumKoval\Webhooks\Support\EventBuffer;

class WebhooksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/webhooks.php', 'webhooks');

        $this->app->singleton(WebhookRegistry::class, fn () => new WebhookRegistry());
        $this->app->singleton(EventBuffer::class, fn () => new EventBuffer());
        $this->app->singleton(WebhookDispatcher::class, function ($app) {
            return new WebhookDispatcher(
                $app->make(WebhookRegistry::class),
                $app->make(EventBuffer::class)
            );
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'webhooks');

        $this->publishes([
            __DIR__ . '/../config/webhooks.php' => config_path('webhooks.php'),
        ], 'webhooks-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/webhooks'),
        ], 'webhooks-views');

        $this->app->singleton(EventBridge::class, function ($app) {
            return new EventBridge(
                $app->make(WebhookRegistry::class),
                $app->make(WebhookDispatcher::class)
            );
        });

        $this->app->singleton(ScheduleBridge::class, function ($app) {
            return new ScheduleBridge(
                $app->make(WebhookRegistry::class),
                $app->make(WebhookDispatcher::class),
                $app->make(\Illuminate\Console\Scheduling\Schedule::class)
            );
        });

        $this->app->booted(function () {
            app(EventBridge::class)->register();

            if ($this->app->runningInConsole()) {
                app(ScheduleBridge::class)->register();
            }
        });
    }
}
