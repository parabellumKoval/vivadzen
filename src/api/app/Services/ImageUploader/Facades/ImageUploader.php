<?php

namespace App\Services\ImageUploader\Facades;

use Illuminate\Support\Facades\Facade;

class ImageUploader extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'imageuploader';
    }
}