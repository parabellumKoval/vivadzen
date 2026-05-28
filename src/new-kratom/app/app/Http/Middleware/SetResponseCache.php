<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Прибиваем Cache-Control / Vary для HTML-страниц фронта.
 *
 * - Главная и каталог: public, max-age=60, stale-while-revalidate=600
 *   Год нет смысла кешить, но 60 секунд — это огромный выигрыш для
 *   повторных визитов / пред-просмотра в Google.
 * - Чекаут, корзина: no-store (per-user state).
 * - API-эндпоинты (admin/order status): no-store, мониторятся отдельно.
 */
class SetResponseCache
{
    private const PUBLIC_PATHS = [
        '/' => true,
        '/kratom' => true,
        '/styleguide' => true,
        '/doruceni' => true,
        '/laboratorni-testy' => true,
        '/licence' => true,
        '/prodejny' => true,
        '/reklamace' => true,
        '/kontakt' => true,
        '/podpora' => true,
        '/o-nas' => true,
        '/obchodni-podminky' => true,
        '/ochrana-osobnich-udaju' => true,
        '/cookies' => true,
        '/predplatne' => true,
        '/pruvodce' => true,
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Только GET-страницы (не API, не form-submit)
        if ($request->method() !== 'GET') {
            return $response;
        }

        $path = '/'.ltrim($request->path(), '/');
        $isPublicPath = isset(self::PUBLIC_PATHS[$path])
            || str_starts_with($path, '/kratom/'); // /kratom/{slug|taxonomy}

        if ($isPublicPath) {
            $response->headers->set(
                'Cache-Control',
                'public, max-age=60, s-maxage=300, stale-while-revalidate=600'
            );
            $response->headers->set('Vary', 'Accept-Language, Accept-Encoding');
        } elseif (str_starts_with($path, 'kosik') || str_starts_with($path, 'pokladna')) {
            $response->headers->set('Cache-Control', 'private, no-store');
        }

        return $response;
    }
}
