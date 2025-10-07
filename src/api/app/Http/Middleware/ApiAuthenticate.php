<?php
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate;

class ApiAuthenticate extends Authenticate
{
    protected function redirectTo($request): ?string
    {
        // отключаем редирект на login
        return null;
    }
}
