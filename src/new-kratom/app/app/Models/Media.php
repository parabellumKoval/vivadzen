<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'disk', 'path', 'filename', 'mime', 'size',
        'alt', 'renditions', 'uploaded_by',
    ];

    protected $casts = [
        'renditions' => 'array',
        'size' => 'integer',
    ];
}
