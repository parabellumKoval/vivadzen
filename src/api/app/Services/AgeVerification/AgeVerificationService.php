<?php

namespace App\Services\AgeVerification;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class AgeVerificationService
{
    protected ?array $configuredCategoryIds = null;
    protected ?array $expandedCategoryIds = null;

    public function isEnabled(): bool
    {
        return (bool) \Settings::get('store.age_verification.adulto.enabled', false);
    }

    public function isCountryApplicable(?string $country = null): bool
    {
        $countryCode = strtolower((string) ($country ?? request()->get('country') ?? ''));

        return $countryCode === 'cz';
    }

    public function productRequiresVerification($product): bool
    {
        $requiredCategoryIds = $this->getExpandedCategoryIds();

        if (empty($requiredCategoryIds) || !$product) {
            return false;
        }

        $categories = $this->resolveProductCategories($product);

        if (!$categories || !$categories->count()) {
            return false;
        }

        $productCategoryIds = $categories->pluck('id')->map(static function ($id) {
            return (int) $id;
        })->unique()->all();

        return !empty(array_intersect($requiredCategoryIds, $productCategoryIds));
    }

    public function productsRequireVerification(iterable $products): bool
    {
        foreach ($products as $product) {
            if ($this->productRequiresVerification($product)) {
                return true;
            }
        }

        return false;
    }

    public function orderRequiresVerification(iterable $products, ?string $country = null): bool
    {
        if (!$this->isEnabled() || !$this->isCountryApplicable($country)) {
            return false;
        }

        return $this->productsRequireVerification($products);
    }

    public function getConfiguredCategoryIds(): array
    {
        if ($this->configuredCategoryIds !== null) {
            return $this->configuredCategoryIds;
        }

        $raw = \Settings::get('store.age_verification.adulto.category_ids', []);

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = json_last_error() === JSON_ERROR_NONE ? $decoded : [$raw];
        }

        $ids = collect($raw ?? [])
            ->map(static fn($id) => (int) $id)
            ->filter(static fn($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $this->configuredCategoryIds = $ids;

        return $this->configuredCategoryIds;
    }

    public function getExpandedCategoryIds(): array
    {
        if ($this->expandedCategoryIds !== null) {
            return $this->expandedCategoryIds;
        }

        $baseIds = $this->getConfiguredCategoryIds();

        if (empty($baseIds)) {
            $this->expandedCategoryIds = [];

            return $this->expandedCategoryIds;
        }

        $categoryClass = \Settings::get('dress.category.model', \Backpack\Store\app\Models\Category::class);
        $expanded = collect($baseIds);

        if (is_string($categoryClass) && class_exists($categoryClass) && method_exists($categoryClass, 'getCategoryNodeIdList')) {
            foreach ($baseIds as $categoryId) {
                $nodeIds = $categoryClass::getCategoryNodeIdList(id: (int) $categoryId);

                if (is_array($nodeIds) && !empty($nodeIds)) {
                    $expanded = $expanded->merge($nodeIds);
                }
            }
        }

        $this->expandedCategoryIds = $expanded
            ->map(static fn($id) => (int) $id)
            ->filter(static fn($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        return $this->expandedCategoryIds;
    }

    protected function resolveProductCategories($product): Collection
    {
        if (!$product) {
            return collect();
        }

        if (method_exists($product, 'relationLoaded') && $product->relationLoaded('categories')) {
            $loaded = method_exists($product, 'getRelation')
                ? $product->getRelation('categories')
                : ($product->categories ?? []);

            if ($loaded instanceof Collection) {
                return $loaded;
            }

            return collect($loaded);
        }

        if (!method_exists($product, 'categories')) {
            return collect();
        }

        $categoriesResult = $product->categories();

        if ($categoriesResult instanceof Relation) {
            $loaded = $categoriesResult->getResults();

            if ($loaded instanceof Collection) {
                return $loaded;
            }

            return collect($loaded);
        }

        if ($categoriesResult instanceof Collection) {
            return $categoriesResult;
        }

        if (is_array($categoriesResult)) {
            return collect($categoriesResult);
        }

        return collect();
    }
}
