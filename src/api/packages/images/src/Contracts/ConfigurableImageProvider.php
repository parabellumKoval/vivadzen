<?php

namespace ParabellumKoval\BackpackImages\Contracts;

interface ConfigurableImageProvider extends ImageStorageProvider
{
    public static function fromConfig(array $config): static;
}
