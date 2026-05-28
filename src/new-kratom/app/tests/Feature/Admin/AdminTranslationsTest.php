<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\Product;
use App\Models\Taxonomy;
use App\Services\CacheWarmer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class AdminTranslationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $warmer = Mockery::mock(CacheWarmer::class);
        $warmer->shouldReceive('warmProduct')->andReturnNull();
        $warmer->shouldReceive('evictProduct')->andReturnNull();
        $warmer->shouldReceive('warmTaxonomy')->andReturnNull();
        $warmer->shouldReceive('warmTaxonomies')->andReturnNull();
        $this->app->instance(CacheWarmer::class, $warmer);

        $admin = AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret123',
            'role' => 'admin',
        ]);

        Sanctum::actingAs($admin, ['admin']);
    }

    public function test_product_endpoint_persists_translatable_content_payloads(): void
    {
        $response = $this->postJson('/admin-api/products', [
            'slug' => 'zelena-maeng-da-test',
            'name' => ['cs' => 'Zelena', 'en' => 'Green', 'ru' => 'Зелёная', 'uk' => 'Зелена'],
            'short' => ['cs' => 'Kratky', 'en' => 'Short', 'ru' => 'Коротко', 'uk' => 'Коротко'],
            'description' => ['cs' => 'Popis', 'en' => 'Description', 'ru' => 'Описание', 'uk' => 'Опис'],
            'origin' => ['cs' => 'Borneo', 'en' => 'Borneo', 'ru' => 'Борнео', 'uk' => 'Борнео'],
            'grind' => ['cs' => 'jemne mlety', 'en' => 'fine ground', 'ru' => 'мелкий помол', 'uk' => 'дрібний помел'],
            'form_slug' => 'prasek',
            'variants' => [
                ['size' => 25, 'unit' => 'g', 'price' => 290, 'stock' => 3, 'sku' => 'SKU-25'],
            ],
            'in_stock' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.origin.ru', 'Борнео')
            ->assertJsonPath('data.grind.uk', 'дрібний помел');

        $product = Product::query()->where('slug', 'zelena-maeng-da-test')->firstOrFail();

        $this->assertSame('Borneo', $product->origin);
        $this->assertSame([
            'cs' => 'Borneo',
            'en' => 'Borneo',
            'ru' => 'Борнео',
            'uk' => 'Борнео',
        ], $product->origin_i18n);
        $this->assertSame([
            'cs' => 'jemne mlety',
            'en' => 'fine ground',
            'ru' => 'мелкий помол',
            'uk' => 'дрібний помел',
        ], $product->grind_i18n);
    }

    public function test_taxonomy_endpoint_persists_translatable_meta_payloads(): void
    {
        $response = $this->postJson('/admin-api/taxonomies', [
            'type' => 'color',
            'slug' => 'modry',
            'label' => ['cs' => 'Modry', 'en' => 'Blue', 'ru' => 'Синий', 'uk' => 'Синій'],
            'description' => ['cs' => 'Popis', 'en' => 'Description', 'ru' => 'Описание', 'uk' => 'Опис'],
            'meta' => [
                'h1_i18n' => ['cs' => 'Modry kratom', 'en' => 'Blue kratom', 'ru' => 'Синий кратом', 'uk' => 'Синій кратом'],
                'dose_i18n' => ['cs' => '3-5 g', 'en' => '3-5 g', 'ru' => '3-5 г', 'uk' => '3-5 г'],
                'vein' => 'blue',
                'accent' => 'ocean',
                'rangeMin' => '1,1',
                'rangeMax' => '1,4',
                'comingSoon' => true,
            ],
            'position' => 10,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.meta.h1_i18n.ru', 'Синий кратом')
            ->assertJsonPath('data.meta.comingSoon', true);

        $taxonomy = Taxonomy::query()->where('slug', 'modry')->firstOrFail();

        $this->assertSame('Modry kratom', $taxonomy->meta['h1']);
        $this->assertSame([
            'cs' => 'Modry kratom',
            'en' => 'Blue kratom',
            'ru' => 'Синий кратом',
            'uk' => 'Синій кратом',
        ], $taxonomy->meta['h1_i18n']);
        $this->assertTrue($taxonomy->meta['comingSoon']);
    }
}
