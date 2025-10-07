<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $table = 'ak_admin_users';
    protected $fillable = ['name','email','password'];
    protected $hidden = ['password','remember_token'];
}
