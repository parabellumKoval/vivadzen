<?php

namespace App\Models;

class AiGenerationHistory extends BaseHistoryModel
{
    protected $table = 'ai_generation_history';

    public function generatable()
    {
        return $this->morphTo();
    }

    protected static function getMorphField(): string
    {
        return 'generatable';
    }
}
