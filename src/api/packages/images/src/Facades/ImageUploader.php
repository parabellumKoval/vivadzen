<?php

namespace ParabellumKoval\BackpackImages\Facades;

use Illuminate\Support\Facades\Facade;

class ImageUploader extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'backpack-images.uploader';
    }
}
