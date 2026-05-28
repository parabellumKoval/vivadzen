<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoaFile extends Model
{
    protected $fillable = [
        'product_id', 'batch', 'tested_at', 'lab_name',
        'mitragynin', 'h7mg', 'purity', 'pdf_path', 'summary', 'published_at',
    ];

    protected $casts = [
        'summary' => 'array',
        'tested_at' => 'date',
        'published_at' => 'datetime',
        'mitragynin' => 'decimal:2',
        'h7mg' => 'decimal:3',
        'purity' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
