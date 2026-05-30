<?php

namespace App\Models;

use App\Services\MediaStorage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'disk', 'path', 'url', 'alt', 'title', 'renditions', 'position'];

    protected $casts = [
        'renditions' => 'array',
        'position' => 'integer',
    ];

    protected $appends = ['public_url'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Готовый публичный URL — берётся из колонки url, а если её нет (старые записи),
     * собирается через MediaStorage из disk/path.
     */
    protected function publicUrl(): Attribute
    {
        return Attribute::get(function () {
            if (! empty($this->url)) {
                return $this->url;
            }

            if (! $this->path) {
                return null;
            }

            return app(MediaStorage::class)->url($this->disk ?: 'public', $this->path);
        });
    }
}
