<?php

namespace ParabellumKoval\BackpackImages\Support;

use InvalidArgumentException;
use ParabellumKoval\BackpackImages\Contracts\ImageStorageProvider;

class ImageProviderRegistry
{
    /** @var array<string, array> */
    protected array $definitions;

    /** @var array<string, ImageStorageProvider> */
    protected array $resolved = [];

    public function __construct(array $definitions, private readonly ImageProviderFactory $factory)
    {
        $this->definitions = $definitions;
    }

    public function register(string $name, array $config): void
    {
        $this->definitions[$name] = $config;
        unset($this->resolved[$name]);
    }

    public function resolve(string $name): ImageStorageProvider
    {
        if (!isset($this->resolved[$name])) {
            $this->resolved[$name] = $this->create($name);
        }

        return $this->resolved[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->definitions[$name]);
    }

    /**
     * @return array<string, ImageStorageProvider>
     */
    public function all(): array
    {
        foreach (array_keys($this->definitions) as $name) {
            $this->resolve($name);
        }

        return $this->resolved;
    }

    protected function create(string $name): ImageStorageProvider
    {
        if (!isset($this->definitions[$name])) {
            throw new InvalidArgumentException(sprintf('Image storage provider "%s" is not defined.', $name));
        }

        return $this->factory->make($name, $this->definitions[$name]);
    }
}
