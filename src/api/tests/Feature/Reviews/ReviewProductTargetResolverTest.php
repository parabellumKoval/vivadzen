<?php

namespace Tests\Feature\Reviews;

use App\Models\Product;
use Backpack\Reviews\app\Services\ReviewProductTargetResolver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReviewProductTargetResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('ak_products');

        Schema::create('ak_products', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('slug')->nullable();
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function test_resolver_returns_base_product_and_family_ids(): void
    {
        DB::table('ak_products')->insert([
            [
                'id' => 1,
                'parent_id' => null,
                'slug' => 'base',
                'name' => json_encode(['ru' => 'Base'], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'parent_id' => 1,
                'slug' => 'mod-a',
                'name' => json_encode(['ru' => 'Mod A'], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'parent_id' => 1,
                'slug' => 'mod-b',
                'name' => json_encode(['ru' => 'Mod B'], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $base = Product::query()->without(['categories', 'ap', 'suppliers', 'parent'])->findOrFail(1);
        $modA = Product::query()->without(['categories', 'ap', 'suppliers', 'parent'])->findOrFail(2);
        $modB = Product::query()->without(['categories', 'ap', 'suppliers', 'parent'])->findOrFail(3);

        $resolver = app(ReviewProductTargetResolver::class);

        $this->assertSame($base->id, $resolver->canonicalProductId($modA));
        $this->assertSame([$modA->id, $base->id, $modB->id], $resolver->familyProductIds($modA));

        $uniqueProducts = $resolver->uniqueCanonicalProducts(collect([$base, $modA, $modB]));

        $this->assertCount(1, $uniqueProducts);
        $this->assertSame($base->id, $uniqueProducts->first()->id);
    }
}
