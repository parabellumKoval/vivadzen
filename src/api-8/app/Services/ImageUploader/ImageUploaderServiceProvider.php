<?php

namespace App\Services\ImageUploader;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class ImageUploaderServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Регистрируем файл конфигурации под ключом 'imageupload'
        $this->mergeConfigFrom(__DIR__ . '/../../../config/imageupload.php', 'imageupload');

        // Биндим провайдеры хранения в контейнер как синглтоны (один экземпляр на приложение)
        $this->app->singleton(LocalStorageProvider::class, function ($app) {
            return new LocalStorageProvider(
                config('imageupload.local_disk'),
                config('imageupload.local_url_prefix')
            );
        });
        $this->app->singleton(BunnyCDNStorageProvider::class, function ($app) {
            return new BunnyCDNStorageProvider(
                config('imageupload.bunny_storage_zone'),
                config('imageupload.bunny_api_key'),
                config('imageupload.bunny_region'),
                config('imageupload.bunny_root_folder'),
                config('imageupload.bunny_pull_zone_url')
            );
        });

        // Регистрируем основной сервис ImageUploaderService
        $this->app->singleton(ImageUploaderService::class, function ($app) {
            return new ImageUploaderService(
                $app->make(LocalStorageProvider::class),
                $app->make(BunnyCDNStorageProvider::class)
            );
        });
        // Также связываем абстракцию 'imageuploader' с нашим сервисом для фасада
        $this->app->alias(ImageUploaderService::class, 'imageuploader');

        // Регистрируем псевдоним фасада через AliasLoader, чтобы можно было использовать \ImageUploader::
        AliasLoader::getInstance()->alias('ImageUploader', \App\Services\ImageUploader\Facades\ImageUploader::class);
    }

    public function boot()
    {
        // Если бы это был пакет, здесь можно было бы разместить публикацию конфига:
        // $this->publishes([__DIR__.'/../../config/imageupload.php' => config_path('imageupload.php')], 'config');
    }
}
