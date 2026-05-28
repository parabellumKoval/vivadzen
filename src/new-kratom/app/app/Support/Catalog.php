<?php

namespace App\Support;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\TaxonomyRepositoryInterface;

/**
 * Тонкий facade поверх RedisProductRepository / RedisTaxonomyRepository.
 *
 * НЕ обращается к MySQL ни в каком сценарии. Если Redis-проекция
 * не прогрета — возвращает пустые массивы, что считается "каталог
 * временно пуст" и обрабатывается шаблонами.
 *
 * Существующая сигнатура методов сохранена для обратной совместимости
 * с views/Cart/контроллерами, написанными до перехода на репозитории.
 */
final class Catalog
{
    /** @return array<int, array<string, mixed>> */
    public static function products(): array
    {
        return self::repo()->all();
    }

    public static function find(string $slug): ?array
    {
        return self::repo()->find($slug);
    }

    /** @return array<int, array<string, mixed>> */
    public static function related(string $slug, int $limit = 4): array
    {
        return self::repo()->related($slug, $limit);
    }

    /** @return array<string, array<string, mixed>> */
    public static function colors(): array
    {
        return self::taxonomies()->byType('color');
    }

    /** @return array<string, array<string, mixed>> */
    public static function strains(): array
    {
        return self::taxonomies()->byType('strain');
    }

    /** @return array<string, array<string, mixed>> */
    public static function forms(): array
    {
        return self::taxonomies()->byType('form');
    }

    /** @return array{type:string, key:string, data:array<string,mixed>}|null */
    public static function resolveTaxonomy(string $segment): ?array
    {
        return self::taxonomies()->resolveSegment($segment);
    }

    /** @return array<int, array<string, mixed>> */
    public static function placeholders(): array
    {
        // Placeholder-ы не пишутся в БД — они UI-стаб для "Brzy". Оставляем
        // статикой, чтобы дизайнер мог менять без миграции.
        return [
            ['slug' => 'zelena-bali', 'name' => 'Zelená Bali', 'sub' => 'Prášek 25 g', 'color' => 'zeleny', 'vein' => 'green'],
            ['slug' => 'cerveny-borneo', 'name' => 'Červený Borneo', 'sub' => 'Prášek 50 g', 'color' => 'cerveny', 'vein' => 'red'],
            ['slug' => 'zluty-vietnam', 'name' => 'Žlutý Vietnam', 'sub' => 'Prášek 25 g', 'color' => 'zluty', 'vein' => 'yellow'],
            ['slug' => 'bila-thai-elephant', 'name' => 'Bílá Thai Elephant', 'sub' => 'Prášek 50 g', 'color' => 'bily', 'vein' => 'white'],
            ['slug' => 'extrakt-30ml-cerveny', 'name' => 'Extrakt 30 ml červený', 'sub' => 'Lim. edice', 'color' => 'cerveny', 'vein' => 'red'],
            ['slug' => 'sumatra-nano', 'name' => 'Sumatra Nano', 'sub' => 'Jemně mletý', 'color' => 'zeleny', 'vein' => 'green'],
        ];
    }

    private static function repo(): ProductRepositoryInterface
    {
        return app(ProductRepositoryInterface::class);
    }

    private static function taxonomies(): TaxonomyRepositoryInterface
    {
        return app(TaxonomyRepositoryInterface::class);
    }
}
