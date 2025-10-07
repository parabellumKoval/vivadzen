<?php

namespace ParabellumKoval\BackpackImages\Support;

use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use ParabellumKoval\BackpackImages\Contracts\ConfigurableImageProvider;
use ParabellumKoval\BackpackImages\Contracts\ImageStorageProvider;

class ImageProviderFactory
{
    public function __construct(private readonly Container $container)
    {
    }

    public function make(string $name, array $config): ImageStorageProvider
    {
        $driver = $config['driver'] ?? null;

        if (!is_string($driver) || $driver === '') {
            throw new InvalidArgumentException(sprintf('Image storage provider "%s" is missing a driver class.', $name));
        }

        if (!is_subclass_of($driver, ImageStorageProvider::class)) {
            throw new InvalidArgumentException(sprintf('Image storage provider "%s" must implement %s.', $name, ImageStorageProvider::class));
        }

        if (is_subclass_of($driver, ConfigurableImageProvider::class)) {
            return $driver::fromConfig($config);
        }

        return $this->container->make($driver, $config['arguments'] ?? []);
    }
}
