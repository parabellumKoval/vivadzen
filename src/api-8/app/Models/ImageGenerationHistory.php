<?php

namespace App\Models;

class ImageGenerationHistory extends BaseHistoryModel
{
    protected $table = 'image_generation_history';

    public function generatable()
    {
        return $this->morphTo();
    }

    protected static function getMorphField(): string
    {
        return 'generatable';
    }
}
