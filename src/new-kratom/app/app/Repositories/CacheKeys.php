<?php

namespace App\Repositories;

/**
 * Централизованные ключи Redis. Версионирование (v1) встроено в префикс —
 * при breaking-изменении схемы кеша поднимаем версию и старая проекция
 * сама "истечёт" без ручного flush.
 */
final class CacheKeys
{
    public const VERSION = 'v1';

    public static function product(string $slug): string
    {
        return self::ns("product:{$slug}");
    }

    public static function productList(): string
    {
        return self::ns('product:_index');
    }

    public static function taxonomy(string $type, string $key): string
    {
        return self::ns("taxonomy:{$type}:{$key}");
    }

    public static function taxonomyIndex(string $type): string
    {
        return self::ns("taxonomy:{$type}:_index");
    }

    public static function catalogByTaxonomy(string $type, string $key): string
    {
        return self::ns("catalog:{$type}:{$key}");
    }

    public static function catalogAll(): string
    {
        return self::ns('catalog:all');
    }

    public static function navigation(): string
    {
        return self::ns('navigation');
    }

    public static function homeSections(): string
    {
        return self::ns('home:sections');
    }

    private static function ns(string $key): string
    {
        return 'nk:'.self::VERSION.':'.$key;
    }
}
