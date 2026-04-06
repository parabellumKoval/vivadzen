<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductRegionalContent;
use Tests\TestCase;

class ProductTranslationInheritanceTest extends TestCase
{
    public function test_effective_regionalized_translations_merge_child_and_parent_by_locale(): void
    {
        $parent = $this->makeProduct([
            'content' => [
                'en' => 'Parent EN content',
                'cs' => 'Parent CS content',
            ],
        ]);

        $child = $this->makeProduct([
            'content' => [
                'es' => 'Child ES fallback content',
            ],
        ], $parent);

        $this->attachRegionalContent($parent, 'cz', [
            'content' => [
                'en' => 'Parent CZ EN regional content',
                'cs' => 'Parent CZ CS regional content',
            ],
        ]);

        $this->attachRegionalContent($child, 'cz', [
            'content' => [
                'es' => 'Child CZ ES regional content',
            ],
        ]);

        $translations = $child->getEffectiveRegionalizedTranslations('content', 'cz');

        $this->assertSame([
            'es' => 'Child CZ ES regional content',
            'en' => 'Parent CZ EN regional content',
            'cs' => 'Parent CZ CS regional content',
        ], $translations);
    }

    public function test_effective_translations_merge_child_and_parent_by_locale(): void
    {
        $parent = $this->makeProduct([
            'name' => [
                'en' => 'Parent EN name',
                'cs' => 'Parent CS name',
            ],
            'seo' => [
                'en' => [
                    'meta_title' => 'Parent EN title',
                ],
                'cs' => [
                    'meta_title' => 'Parent CS title',
                ],
            ],
        ]);

        $child = $this->makeProduct([
            'name' => [
                'es' => 'Child ES name',
            ],
            'seo' => [
                'es' => [
                    'meta_title' => 'Child ES title',
                ],
            ],
        ], $parent);

        $this->assertSame([
            'es' => 'Child ES name',
            'en' => 'Parent EN name',
            'cs' => 'Parent CS name',
        ], $child->getEffectiveTranslations('name'));

        $this->assertSame([
            'es' => [
                'meta_title' => 'Child ES title',
            ],
            'en' => [
                'meta_title' => 'Parent EN title',
            ],
            'cs' => [
                'meta_title' => 'Parent CS title',
            ],
        ], $child->getEffectiveTranslations('seo'));
    }

    private function makeProduct(array $translations = [], ?Product $parent = null): Product
    {
        $product = new Product();

        foreach ($translations as $attribute => $values) {
            $product->setTranslations($attribute, $values);
        }

        if ($parent) {
            $product->parent_id = 999;
            $product->setRelation('parent', $parent);
        }

        return $product;
    }

    private function attachRegionalContent(Product $product, string $countryCode, array $translations = []): void
    {
        $regional = new ProductRegionalContent([
            'country_code' => $countryCode,
        ]);

        foreach ($translations as $attribute => $values) {
            $regional->setTranslations($attribute, $values);
        }

        $product->setRelation('regionalContents', collect([$regional]));
    }
}
