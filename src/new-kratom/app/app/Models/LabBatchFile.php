<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabBatchFile extends Model
{
    protected $fillable = [
        'lab_batch_id', 'disk', 'path', 'url', 'original_name',
        'file_no', 'label', 'tested_at', 'size', 'position',
    ];

    protected $casts = [
        'tested_at' => 'date',
        'size' => 'integer',
        'position' => 'integer',
    ];

    public function labBatch(): BelongsTo
    {
        return $this->belongsTo(LabBatch::class);
    }

    /**
     * Готовый URL с учётом disk (public-prefix или абсолютный bunny CDN-URL).
     */
    public function getPublicUrlAttribute(): string
    {
        if ($this->url) {
            return $this->url;
        }
        return app(\App\Services\MediaStorage::class)->url($this->disk ?? 'public', $this->path);
    }
}
