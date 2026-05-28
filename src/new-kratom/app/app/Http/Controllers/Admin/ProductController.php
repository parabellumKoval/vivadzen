<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Locale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->with(['variants', 'images']);

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('slug', 'like', "%{$search}%")
                  ->orWhere('batch', 'like', "%{$search}%");
            });
        }

        if ($color = $request->query('color')) {
            $query->where('color_slug', $color);
        }

        $products = $query->orderBy('position')->paginate($request->integer('per_page', 25));
        $products->getCollection()->transform(fn (Product $product) => $this->mapProductForAdmin($product));

        return response()->json(['data' => $products]);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'data' => $this->mapProductForAdmin($product->load(['variants', 'images', 'taxonomies', 'coa'])),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateProduct($request);

        $product = Product::create($data['product']);
        $this->syncVariants($product, $data['variants']);

        // Observer прогреет Redis автоматически

        return response()->json([
            'data' => $this->mapProductForAdmin($product->load(['variants', 'images'])),
        ], 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $this->validateProduct($request);
        $product->update($data['product']);
        $this->syncVariants($product, $data['variants']);

        return response()->json(['data' => $this->mapProductForAdmin($product->fresh(['variants', 'images']))]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json(['ok' => true]);
    }

    private function validateProduct(Request $request): array
    {
        $rules = [
            'slug' => 'required|string|max:128',
            'name' => 'required|array',
            'short' => 'nullable|array',
            'description' => 'nullable|array',
            'origin' => 'nullable|array',
            'color_slug' => 'nullable|string|max:32',
            'strain_slug' => 'nullable|string|max:32',
            'form_slug' => 'nullable|string|max:32',
            'mitragynin' => 'nullable|numeric|min:0|max:99.99',
            'h7mg' => 'nullable|numeric|min:0|max:9.999',
            'purity' => 'nullable|numeric|min:0|max:100',
            'batch' => 'nullable|string|max:64',
            'tested_at' => 'nullable|date',
            'grind' => 'nullable|array',
            'rating' => 'nullable|numeric|min:0|max:5',
            'reviews_count' => 'nullable|integer|min:0',
            'questions_count' => 'nullable|integer|min:0',
            'in_stock' => 'boolean',
            'badge' => 'nullable|array',
            'main_image' => 'nullable|string',
            'position' => 'nullable|integer',
            'published_at' => 'nullable|date',
            // variants
            'variants' => 'array',
            'variants.*.size' => 'required|integer|min:1',
            'variants.*.unit' => 'nullable|string|in:g,ml',
            'variants.*.price' => 'required|integer|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
            'variants.*.sku' => 'nullable|string|max:64',
        ];

        foreach (Locale::SUPPORTED as $locale) {
            $required = $locale === Locale::DEFAULT ? 'required' : 'nullable';

            $rules["name.{$locale}"] = "{$required}|string|max:255";
            $rules["short.{$locale}"] = 'nullable|string|max:255';
            $rules["description.{$locale}"] = 'nullable|string';
            $rules["origin.{$locale}"] = 'nullable|string|max:128';
            $rules["grind.{$locale}"] = 'nullable|string|max:64';
        }

        $validated = $request->validate($rules);
        $variants = $validated['variants'] ?? [];
        unset($validated['variants']);

        $originTranslations = $this->normalizeTranslations($validated['origin'] ?? null);
        $grindTranslations = $this->normalizeTranslations($validated['grind'] ?? null);

        $validated['origin'] = $this->translateTranslations($originTranslations);
        $validated['origin_i18n'] = $originTranslations !== [] ? $originTranslations : null;
        $validated['grind'] = $this->translateTranslations($grindTranslations);
        $validated['grind_i18n'] = $grindTranslations !== [] ? $grindTranslations : null;

        return [
            'product' => $validated,
            'variants' => $variants,
        ];
    }

    /** @return array<string, mixed> */
    private function mapProductForAdmin(Product $product): array
    {
        $payload = $product->toArray();
        $payload['origin'] = $this->translationsForAdmin($product->origin_i18n, $product->origin);
        $payload['grind'] = $this->translationsForAdmin($product->grind_i18n, $product->grind);

        return $payload;
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

    private function syncVariants(Product $product, array $variants): void
    {
        $kept = [];
        foreach ($variants as $v) {
            $variant = ProductVariant::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'size' => $v['size'],
                    'unit' => $v['unit'] ?? 'g',
                ],
                [
                    'price' => $v['price'],
                    'stock' => $v['stock'] ?? 0,
                    'sku' => $v['sku'] ?? null,
                ]
            );
            $kept[] = $variant->id;
        }
        $product->variants()->whereNotIn('id', $kept)->delete();
    }
}
