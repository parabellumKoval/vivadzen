<?php

namespace App\Repositories;

use App\Repositories\Contracts\TaxonomyRepositoryInterface;
use App\Support\Locale;
use Illuminate\Support\Facades\Redis;

class RedisTaxonomyRepository implements TaxonomyRepositoryInterface
{
    public function byType(string $type): array
    {
        $raw = Redis::get(CacheKeys::taxonomyIndex($type));
        if (! $raw) return [];

        $keys = json_decode($raw, true);
        $out = [];
        foreach ($keys as $key) {
            $item = $this->find($type, $key);
            if ($item) {
                $out[$key] = $item;
            }
        }
        return $out;
    }

    public function find(string $type, string $key): ?array
    {
        $raw = Redis::get(CacheKeys::taxonomy($type, $key));
        return $raw ? $this->localizeTaxonomy(json_decode($raw, true)) : null;
    }

    public function resolveSegment(string $segment): ?array
    {
        foreach (['color', 'strain', 'form', 'region'] as $type) {
            if ($data = $this->find($type, $segment)) {
                return ['type' => $type, 'key' => $segment, 'data' => $data];
            }
        }
        return null;
    }

    /** @param array<string, mixed> $taxonomy */
    private function localizeTaxonomy(array $taxonomy): array
    {
        $taxonomy['label'] = $this->translateField($taxonomy['label_i18n'] ?? $taxonomy['label'] ?? null);
        $taxonomy['description'] = $this->translateField($taxonomy['description_i18n'] ?? $taxonomy['description'] ?? null);
        $taxonomy['h1'] = $this->translateField($taxonomy['h1_i18n'] ?? $taxonomy['h1'] ?? null);
        $taxonomy['origin'] = $this->translateField($taxonomy['origin_i18n'] ?? $taxonomy['origin'] ?? null);
        $taxonomy['dose'] = $this->translateField($taxonomy['dose_i18n'] ?? $taxonomy['dose'] ?? null);
        $taxonomy['sub'] = $this->translateField($taxonomy['sub_i18n'] ?? $taxonomy['sub'] ?? null);
        $taxonomy['vein'] = $taxonomy['vein'] ?? $this->veinForSlug($taxonomy['slug'] ?? null);

        return $taxonomy;
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
