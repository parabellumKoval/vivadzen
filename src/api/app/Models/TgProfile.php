<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TgProfile extends Model
{
    protected $table = 'ak_tg_profiles';

    protected $guarded = ['id'];

    protected $casts = [
        'telegram_user_id' => 'integer',
        'addresses' => 'array',
    ];
}
