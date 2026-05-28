<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Taxonomy extends Model
{
    use HasFactory;

    public const TYPE_COLOR = 'color';
    public const TYPE_STRAIN = 'strain';
    public const TYPE_FORM = 'form';
    public const TYPE_REGION = 'region';

    protected $fillable = ['type', 'slug', 'label', 'description', 'meta', 'position'];

    protected $casts = [
        'label' => 'array',
        'description' => 'array',
        'meta' => 'array',
        'position' => 'integer',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_taxonomy');
    }
}
