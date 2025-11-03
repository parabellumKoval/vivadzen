<?php

namespace ParabellumKoval\BackpackImages;

use Illuminate\Support\ServiceProvider;
use ParabellumKoval\BackpackImages\Services\ImageUploader;
use ParabellumKoval\BackpackImages\Support\FileNameGenerator;
use ParabellumKoval\BackpackImages\Support\ImageProviderFactory;
use ParabellumKoval\BackpackImages\Support\ImageProviderRegistry;

class BackpackImagesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/backpack-images.php', 'backpack-images');

        $this->app->singleton(ImageProviderFactory::class, function ($app) {
            return new ImageProviderFactory($app);
        });

        $this->app->singleton(ImageProviderRegistry::class, function ($app) {
            $providers = $app['config']->get('backpack-images.providers', []);

            return new ImageProviderRegistry(
                $providers,
                $app->make(ImageProviderFactory::class)
            );
        });

        $this->app->singleton(FileNameGenerator::class, function () {
            return new FileNameGenerator();
        });

        $this->app->singleton(ImageUploader::class, function ($app) {
            return new ImageUploader(
                $app->make(ImageProviderRegistry::class),
                $app->make(FileNameGenerator::class),
                $app['config']->get('backpack-images', [])
            );
        });

        $this->app->alias(ImageUploader::class, 'backpack-images.uploader');
        $this->app->alias(ImageProviderRegistry::class, 'backpack-images.providers');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/backpack-images.php' => config_path('backpack-images.php'),
        ], 'backpack-images-config');

        $this->loadViewsFrom(__DIR__ . '/resources/views', 'backpack-images');

        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'images');
    }
}
