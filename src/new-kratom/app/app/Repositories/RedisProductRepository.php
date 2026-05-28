<?php

namespace App\Repositories;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Support\Locale;
use Illuminate\Support\Facades\Redis;

/**
 * Чтение каталога ИСКЛЮЧИТЕЛЬНО из Redis. Если ключа нет — возвращает
 * пустой массив (НЕ падает в MySQL). Прогрев — задача CacheWarmer,
 * вызывается из ProductObserver на write, плюс scheduled rebuild раз в день.
 */
class RedisProductRepository implements ProductRepositoryInterface
{
    public function all(): array
    {
        return $this->hydrateList($this->getIndex());
    }

    public function find(string $slug): ?array
    {
        $raw = Redis::get(CacheKeys::product($slug));
        return $raw ? $this->localizeProduct(json_decode($raw, true)) : null;
    }

    public function byTaxonomy(string $type, string $key): array
    {
        $raw = Redis::get(CacheKeys::catalogByTaxonomy($type, $key));
        if ($raw) {
            $slugs = json_decode($raw, true);
            return $this->hydrateList($slugs);
        }
        // Fallback: фильтруем in-memory из общего списка (никакого MySQL)
        return array_values(array_filter($this->all(), function ($p) use ($type, $key) {
            return ($p[$type.'_slug'] ?? null) === $key
                || ($p['color'] ?? null) === $key
                || ($p['strain'] ?? null) === $key
                || ($p['form'] ?? null) === $key;
        }));
    }

    public function related(string $slug, int $limit = 4): array
    {
        $current = $this->find($slug);
        if (! $current) {
            return [];
        }

        $candidates = array_filter($this->all(), fn ($p) => ($p['slug'] ?? null) !== $slug);

        usort($candidates, function ($a, $b) use ($current) {
            $scoreA = ($a['strain'] === $current['strain'] ? 3 : 0)
                + ($a['color'] === $current['color'] ? 2 : 0)
                + ($a['form'] === $current['form'] ? 1 : 0);
            $scoreB = ($b['strain'] === $current['strain'] ? 3 : 0)
                + ($b['color'] === $current['color'] ? 2 : 0)
                + ($b['form'] === $current['form'] ? 1 : 0);
            return $scoreB <=> $scoreA;
        });

        return array_slice(array_values($candidates), 0, $limit);
    }

    /** @return array<int, string> */
    private function getIndex(): array
    {
        $raw = Redis::get(CacheKeys::productList());
        return $raw ? json_decode($raw, true) : [];
    }

    /** @param array<int, string> $slugs */
    private function hydrateList(array $slugs): array
    {
        if (empty($slugs)) return [];

        $keys = array_map(fn ($s) => CacheKeys::product($s), $slugs);
        $values = Redis::mget($keys);
        $out = [];
        foreach ($values as $raw) {
            if ($raw) {
                $out[] = $this->localizeProduct(json_decode($raw, true));
            }
        }
        return $out;
    }

    /** @param array<string, mixed> $product */
    private function localizeProduct(array $product): array
    {
        $product['name'] = $this->translateField($product['name_i18n'] ?? $product['name'] ?? null);
        $product['short'] = $this->translateField($product['short_i18n'] ?? $product['short'] ?? null);
        $product['description'] = $this->translateField($product['description_i18n'] ?? $product['description'] ?? null);
        $product['origin'] = $this->translateField($product['origin_i18n'] ?? $product['origin'] ?? null);
        $product['grind'] = $this->translateField($product['grind_i18n'] ?? $product['grind'] ?? null);
        $product['colorLabel'] = $this->translateField($product['colorLabel_i18n'] ?? $product['colorLabel'] ?? null);
        $product['strainLabel'] = $this->translateField($product['strainLabel_i18n'] ?? $product['strainLabel'] ?? null);
        $product['formLabel'] = $this->translateField($product['formLabel_i18n'] ?? $product['formLabel'] ?? null);
        $product['vein'] = $product['vein'] ?? $this->veinForSlug($product['color'] ?? null);

        return $product;
    }

    private function translateField(array|string|null $value): ?string
    {
        if (method_exists(Locale::class, 'translate')) {
            return Locale::translate($value);
        }

        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed !== '' ? $trimmed : null;
        }

        $locale = Locale::current();
        $candidates = array_values(array_unique([
            $locale,
            Locale::DEFAULT,
            ...array_keys($value),
        ]));

        foreach ($candidates as $candidate) {
            $translated = $value[$candidate] ?? null;

            if (is_string($translated) && trim($translated) !== '') {
                return trim($translated);
            }
        }

        return null;
    }

    private function veinForSlug(?string $slug): ?string
    {
        return match ($slug) {
            'zeleny' => 'green',
            'bily' => 'white',
            'cerveny' => 'red',
            'zluty' => 'yellow',
            default => null,
        };
    }
}
