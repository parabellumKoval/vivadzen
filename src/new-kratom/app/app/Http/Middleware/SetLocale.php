<?php

namespace App\Http\Middleware;

use App\Support\Locale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if ($locale && Locale::isSupported($locale)) {
            app()->setLocale($locale);
        } else {
            app()->setLocale(Locale::DEFAULT);
        }

        view()->share('currentLocale', app()->getLocale());

        return $next($request);
    }
}
