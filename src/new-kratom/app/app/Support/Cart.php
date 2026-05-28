<?php

namespace App\Support;

use Illuminate\Support\Facades\Session;

class Cart
{
    private const KEY = 'cart';
    private const FREE_SHIPPING_AT = 1200;
    private const PROMO_CODES = [
        'KRATOM10' => ['type' => 'percent', 'value' => 10, 'label' => 'KRATOM10 · -10 %'],
        'WELCOME50' => ['type' => 'fixed', 'value' => 50, 'label' => 'WELCOME50 · -50 Kč'],
        'EXPRESS' => ['type' => 'fixed', 'value' => 100, 'label' => 'EXPRESS · -100 Kč'],
    ];

    /** @return array{items: array, promo: ?array, count: int, subtotal: int, discount: int, total: int} */
    public static function snapshot(): array
    {
        $items = self::items();
        $subtotal = collect($items)->sum(fn ($i) => $i['price'] * $i['qty']);
        $promo = self::promo();
        $discount = self::computeDiscount($subtotal, $promo);
        $total = max(0, $subtotal - $discount);

        return [
            'items' => array_values($items),
            'promo' => $promo,
            'count' => (int) collect($items)->sum('qty'),
            'subtotal' => (int) $subtotal,
            'discount' => (int) $discount,
            'total' => (int) $total,
            'free_shipping_at' => self::FREE_SHIPPING_AT,
            'free_shipping_remaining' => max(0, self::FREE_SHIPPING_AT - $total),
        ];
    }

    public static function items(): array
    {
        return Session::get(self::KEY . '.items', []);
    }

    public static function promo(): ?array
    {
        return Session::get(self::KEY . '.promo');
    }

    public static function add(string $slug, int $size, int $qty = 1): array
    {
        $product = Catalog::find($slug);
        if (! $product) {
            return ['error' => 'product_not_found'];
        }

        $size = $size === 50 && (int) ($product['price50'] ?? 0) > 0 ? 50 : 25;
        $price = (int) ($size === 50 ? ($product['price50'] ?? 0) : ($product['price25'] ?? 0));
        if ($price <= 0) {
            return ['error' => 'price_unavailable'];
        }

        $items = self::items();
        $key = $slug . ':' . $size;

        if (isset($items[$key])) {
            $items[$key]['qty'] = min(99, $items[$key]['qty'] + $qty);
        } else {
            $unit = $product['unit'] ?? 'g';
            $items[$key] = [
                'key' => $key,
                'slug' => $slug,
                'name' => $product['name'],
                'image' => $product['image'] ?? null,
                'color' => $product['colorLabel'] ?? '',
                'vein' => $product['vein'] ?? null,
                'strain' => $product['strainLabel'] ?? '',
                'mitragynin' => $product['mitragynin'] ?? null,
                'batch' => $product['batch'] ?? null,
                'grind' => $product['grind'] ?? null,
                'size' => $size,
                'unit' => $unit,
                'price' => $price,
                'price25' => (int) ($product['price25'] ?? 0),
                'price50' => (int) ($product['price50'] ?? 0),
                'qty' => max(1, min(99, $qty)),
            ];
        }

        Session::put(self::KEY . '.items', $items);

        return ['ok' => true];
    }

    public static function update(string $key, ?int $qty = null, ?int $size = null): void
    {
        $items = self::items();
        if (! isset($items[$key])) {
            return;
        }
        $item = $items[$key];

        if ($qty !== null) {
            $item['qty'] = max(0, min(99, $qty));
            if ($item['qty'] === 0) {
                unset($items[$key]);
                Session::put(self::KEY . '.items', $items);
                return;
            }
        }

        if ($size !== null && in_array($size, [25, 50], true) && $size !== $item['size']) {
            unset($items[$key]);
            $item['size'] = $size;
            $item['price'] = $size === 50 ? (int) ($item['price50'] ?? 0) : (int) ($item['price25'] ?? 0);
            $newKey = $item['slug'] . ':' . $size;
            $item['key'] = $newKey;
            // merge if existing
            if (isset($items[$newKey])) {
                $items[$newKey]['qty'] = min(99, $items[$newKey]['qty'] + $item['qty']);
            } else {
                $items[$newKey] = $item;
            }
            Session::put(self::KEY . '.items', $items);
            return;
        }

        $items[$key] = $item;
        Session::put(self::KEY . '.items', $items);
    }

    public static function remove(string $key): void
    {
        $items = self::items();
        unset($items[$key]);
        Session::put(self::KEY . '.items', $items);
    }

    public static function clear(): void
    {
        Session::forget(self::KEY);
    }

    public static function applyPromo(string $code): bool
    {
        $code = strtoupper(trim($code));
        if (! isset(self::PROMO_CODES[$code])) {
            return false;
        }
        Session::put(self::KEY . '.promo', array_merge(self::PROMO_CODES[$code], ['code' => $code]));
        return true;
    }

    public static function removePromo(): void
    {
        Session::forget(self::KEY . '.promo');
    }

    public static function setCheckout(array $data): void
    {
        $existing = Session::get(self::KEY . '.checkout', []);
        Session::put(self::KEY . '.checkout', array_merge($existing, $data));
    }

    public static function checkout(): array
    {
        return Session::get(self::KEY . '.checkout', []);
    }

    private static function computeDiscount(int $subtotal, ?array $promo): int
    {
        if (! $promo) {
            return 0;
        }
        if (($promo['type'] ?? '') === 'percent') {
            return (int) round($subtotal * ($promo['value'] / 100));
        }
        return min($subtotal, (int) ($promo['value'] ?? 0));
    }
}
