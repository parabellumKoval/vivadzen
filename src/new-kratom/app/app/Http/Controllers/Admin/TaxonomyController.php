<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Taxonomy;
use App\Support\Locale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxonomyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Taxonomy::query();
        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        $items = $query->orderBy('type')->orderBy('position')->get()
            ->map(fn (Taxonomy $taxonomy) => $this->mapTaxonomyForAdmin($taxonomy));

        return response()->json([
            'data' => $items,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateTaxonomy($request);
        $tax = Taxonomy::create($data);
        return response()->json(['data' => $this->mapTaxonomyForAdmin($tax)], 201);
    }

    public function update(Request $request, Taxonomy $taxonomy): JsonResponse
    {
        $data = $this->validateTaxonomy($request);
        $taxonomy->update($data);
        return response()->json(['data' => $this->mapTaxonomyForAdmin($taxonomy->fresh())]);
    }

    public function destroy(Taxonomy $taxonomy): JsonResponse
    {
        $taxonomy->delete();
        return response()->json(['ok' => true]);
    }

    private function validateTaxonomy(Request $request): array
    {
        $rules = [
            'type' => 'required|in:color,strain,form,region',
            'slug' => 'required|string|max:64',
            'label' => 'required|array',
            'description' => 'nullable|array',
            'meta' => 'nullable|array',
            'position' => 'nullable|integer',
            'meta.vein' => 'nullable|string|max:32',
            'meta.accent' => 'nullable|string|max:64',
            'meta.rangeMin' => 'nullable|string|max:32',
            'meta.rangeMax' => 'nullable|string|max:32',
            'meta.comingSoon' => 'nullable|boolean',
        ];

        foreach (Locale::SUPPORTED as $locale) {
            $required = $locale === Locale::DEFAULT ? 'required' : 'nullable';

            $rules["label.{$locale}"] = "{$required}|string|max:128";
            $rules["description.{$locale}"] = 'nullable|string';
            $rules["meta.h1_i18n.{$locale}"] = 'nullable|string|max:255';
            $rules["meta.origin_i18n.{$locale}"] = 'nullable|string|max:255';
            $rules["meta.dose_i18n.{$locale}"] = 'nullable|string|max:64';
            $rules["meta.sub_i18n.{$locale}"] = 'nullable|string|max:128';
        }

        $validated = $request->validate($rules);
        $validated['meta'] = $this->normalizeMeta($validated['meta'] ?? []);

        return $validated;
    }

    /** @return array<string, mixed> */
    private function mapTaxonomyForAdmin(Taxonomy $taxonomy): array
    {
        $payload = $taxonomy->toArray();
        $payload['label'] = $this->translationsForAdmin($taxonomy->label);
        $payload['description'] = $this->translationsForAdmin($taxonomy->description);
        $payload['meta'] = $this->metaForAdmin($taxonomy->meta ?? []);

        return $payload;
    }

    /** @return array<string, mixed> */
    private function normalizeMeta(array $meta): array
    {
        foreach (['h1', 'origin', 'dose', 'sub'] as $field) {
            $translationsKey = "{$field}_i18n";
            $translations = $this->normalizeTranslations($meta[$translationsKey] ?? null);

            if ($translations !== []) {
                $meta[$translationsKey] = $translations;
                $meta[$field] = $this->translateTranslations($translations);
            } else {
                unset($meta[$translationsKey]);
            }
        }

        return $meta;
    }

    /** @return array<string, mixed> */
    private function metaForAdmin(array $meta): array
    {
        foreach (['h1', 'origin', 'dose', 'sub'] as $field) {
            $meta["{$field}_i18n"] = $this->translationsForAdmin($meta["{$field}_i18n"] ?? null, $meta[$field] ?? null);
        }

        return $meta;
    }

    /** @return array<string, string> */
    private function translationsForAdmin(?array $translations, ?string $legacy = null): array
    {
        $payload = array_fill_keys(Locale::SUPPORTED, '');

        foreach ($translations ?? [] as $locale => $value) {
            if (in_array($locale, Locale::SUPPORTED, true) && is_string($value)) {
                $payload[$locale] = $value;
            }
        }

        if (($payload[Locale::DEFAULT] ?? '') === '' && $legacy) {
            $payload[Locale::DEFAULT] = $legacy;
        }

        return $payload;
    }

    /** @return array<string, string> */
    private function normalizeTranslations(?array $translations): array
    {
        $normalized = [];

        foreach (Locale::SUPPORTED as $locale) {
            $value = $translations[$locale] ?? null;

            if (is_string($value)) {
                $normalized[$locale] = trim($value);
            }
        }

        return $normalized;
    }

    private function translateTranslations(array $translations): ?string
    {
        if ($translations === []) {
            return null;
        }

        if (method_exists(Locale::class, 'translate')) {
            return Locale::translate($translations, Locale::DEFAULT);
        }

        foreach ([Locale::DEFAULT, ...Locale::SUPPORTED] as $locale) {
            $value = $translations[$locale] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }
}
