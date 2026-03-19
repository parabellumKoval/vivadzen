<?php

namespace ParabellumKoval\Dumper\Services;

use Illuminate\Contracts\Config\Repository;
use Throwable;

class DumperSettings
{
    public function __construct(
        protected Repository $config
    ) {
    }

    public function value(string $key, mixed $default = null): mixed
    {
        $fallback = $this->config->get($key, $default);

        if (!app()->bound('backpack.settings')) {
            return $fallback;
        }

        try {
            return app('backpack.settings')->get($key, $fallback);
        } catch (Throwable) {
            return $fallback;
        }
    }

    /**
     * @param array<string, mixed> $default
     * @return array<string, mixed>
     */
    public function group(string $key, array $default = []): array
    {
        $fallback = $default !== [] ? $default : (array) $this->config->get($key, []);
        $value = $this->value($key, []);

        if (!is_array($value)) {
            return $fallback;
        }

        return $this->mergeRecursive($fallback, $value);
    }

    protected function mergeRecursive(array $defaults, array $overrides): array
    {
        foreach ($overrides as $key => $value) {
            if (is_array($value) && isset($defaults[$key]) && is_array($defaults[$key])) {
                $defaults[$key] = $this->mergeRecursive($defaults[$key], $value);
                continue;
            }

            $defaults[$key] = $value;
        }

        return $defaults;
    }
}
