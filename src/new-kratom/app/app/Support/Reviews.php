<?php

namespace App\Support;

/**
 * Витрина отзывов поверх Redis-проекции каталога (Catalog).
 * НЕ обращается к MySQL — читает уже прогретые превью-отзывы из товаров.
 */
final class Reviews
{
    /**
     * Подборка отзывов для главной: чередуем отзывы о разных товарах,
     * чтобы подряд не шли несколько отзывов об одном товаре. Когда товаров
     * меньше, чем нужно отзывов, товар встречается повторно — но не подряд,
     * пока есть отзывы о других товарах.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function featured(int $limit = 12): array
    {
        $queues = [];

        foreach (Catalog::products() as $product) {
            $reviews = $product['reviews'] ?? [];
            if (empty($reviews)) {
                continue;
            }

            $context = [
                'slug'  => $product['slug'],
                'name'  => $product['name'],
                'url'   => Locale::url('/kratom/' . $product['slug']),
                'image' => $product['image'] ?? null,
                'price' => $product['price25'] ?? $product['price50'] ?? null,
            ];

            // Превью-отзывы из CacheWarmer уже отсортированы published_at desc.
            $items = array_map(
                fn (array $review) => $review + ['product' => $context],
                array_values($reviews)
            );

            $queues[] = [
                'slug'   => $product['slug'],
                'latest' => $reviews[0]['published_at'] ?? '',
                'items'  => $items,
            ];
        }

        // Жадная раскладка: на каждом шаге берём отзыв из товара с наибольшим
        // остатком, но не из того же товара, что и предыдущий. Так несколько
        // отзывов об одном товаре подряд не идут, пока это математически возможно
        // (т.е. пока ни у одного товара не больше половины оставшихся отзывов).
        $result = [];
        $lastSlug = null;

        while (count($result) < $limit) {
            $best = null;
            foreach ($queues as $i => $queue) {
                if (empty($queue['items']) || $queue['slug'] === $lastSlug) {
                    continue;
                }
                if ($best === null
                    || count($queue['items']) > count($queues[$best]['items'])
                    || (count($queue['items']) === count($queues[$best]['items'])
                        && strcmp((string) $queue['latest'], (string) $queues[$best]['latest']) > 0)) {
                    $best = $i;
                }
            }

            // Не нашли другого товара — остался только предыдущий. Берём его
            // (повтор подряд неизбежен), иначе все очереди пусты — выходим.
            if ($best === null) {
                foreach ($queues as $i => $queue) {
                    if (! empty($queue['items'])) {
                        $best = $i;
                        break;
                    }
                }
                if ($best === null) {
                    break;
                }
            }

            $result[] = array_shift($queues[$best]['items']);
            $lastSlug = $queues[$best]['slug'];
        }

        return $result;
    }
}
