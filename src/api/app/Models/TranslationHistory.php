<?php

namespace App\Models;

class TranslationHistory extends BaseHistoryModel
{
    protected $table = 'translation_history';

    public function translatable()
    {
        return $this->morphTo();
    }

    protected static function getMorphField(): string
    {
        return 'translatable';
    }
}
