<?php

namespace Tests\Feature\Reviews;

use App\Models\GenerationRun;
use App\Models\Product;
use Backpack\Reviews\app\Services\GeneratedProductPhotoGenerator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use ParabellumKoval\AiContentGenerator\Services\ContentGenerator;
use Tests\TestCase;

class GenerateProductReviewPhotosCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('ak_generation_runs');
        Schema::dropIfExists('ak_generated_product_photos');
        Schema::dropIfExists('ak_reviews');
        Schema::dropIfExists('ak_category_product');
        Schema::dropIfExists('ak_categories');
        Schema::dropIfExists('ak_brands');
        Schema::dropIfExists('ak_products');

        Schema::create('ak_products', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('slug')->nullable();
            $table->text('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ak_brands', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('ak_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('ak_category_product', function (Blueprint $table): void {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('category_id');
        });

        Schema::create('ak_reviews', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('reviewable_id');
            $table->string('reviewable_type');
            $table->timestamps();
        });

        Schema::create('ak_generated_product_photos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('status', 32)->nullable();
            $table->json('image')->nullable();
            $table->text('prompt')->nullable();
            $table->json('prompt_context')->nullable();
            $table->string('reference_image_url')->nullable();
            $table->string('reference_image_path')->nullable();
            $table->string('driver')->nullable();
            $table->string('model')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('generation_run_id')->nullable();
            $table->timestamps();
        });

        Schema::create('ak_generation_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 64);
            $table->string('status', 32)->nullable();
            $table->string('command', 128);
            $table->unsignedBigInteger('initiator_id')->nullable();
            $table->unsignedInteger('progress_total')->default(0);
            $table->unsignedInteger('progress_current')->default(0);
            $table->json('options')->nullable();
            $table->json('meta')->nullable();
            $table->json('result')->nullable();
            $table->longText('output')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_all_mode_applies_limit_to_canonical_products_without_duplicates(): void
    {
        DB::table('ak_products')->insert([
            [
                'id' => 1,
                'parent_id' => null,
                'brand_id' => null,
                'slug' => 'base-a',
                'name' => json_encode(['ru' => 'Base A'], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'parent_id' => 1,
                'brand_id' => null,
                'slug' => 'mod-a',
                'name' => json_encode(['ru' => 'Mod A'], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'parent_id' => null,
                'brand_id' => null,
                'slug' => 'base-b',
                'name' => json_encode(['ru' => 'Base B'], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'parent_id' => null,
                'brand_id' => null,
                'slug' => 'base-c',
                'name' => json_encode(['ru' => 'Base C'], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->seedReviews(2, 10);
        $this->seedReviews(3, 7);
        $this->seedReviews(1, 5);
        $this->seedReviews(4, 1);

        $generatedProductIds = [];

        $photoGenerator = Mockery::mock(GeneratedProductPhotoGenerator::class);
        $photoGenerator->shouldReceive('generate')
            ->twice()
            ->andReturnUsing(function (Product $product) use (&$generatedProductIds): array {
                $generatedProductIds[] = (int) $product->getKey();

                return ['status' => 'success'];
            });

        $contentGenerator = Mockery::mock(ContentGenerator::class);

        $this->app->instance(GeneratedProductPhotoGenerator::class, $photoGenerator);
        $this->app->instance(ContentGenerator::class, $contentGenerator);

        $run = GenerationRun::query()->create([
            'type' => GenerationRun::TYPE_PRODUCT_REVIEW_PHOTOS,
            'status' => GenerationRun::STATUS_QUEUED,
            'command' => 'reviews:generate-product-photos',
        ]);

        $exitCode = Artisan::call('reviews:generate-product-photos', [
            '--all' => true,
            '--limit' => '2',
            '--photos-per-product' => '1',
            '--run-id' => (string) $run->id,
        ]);

        $run->refresh();

        $this->assertSame(0, $exitCode);
        $this->assertSame([1, 3], $generatedProductIds);
        $this->assertSame(2, (int) $run->progress_total);
        $this->assertSame(2, (int) $run->progress_current);
        $this->assertSame(2, (int) ($run->meta['generated_photos'] ?? 0));
        $this->assertSame(0, (int) ($run->meta['failed_products'] ?? 0));
        $this->assertSame(0, (int) ($run->meta['skipped_products'] ?? 0));
    }

    protected function seedReviews(int $productId, int $count): void
    {
        $rows = [];

        for ($index = 0; $index < $count; $index++) {
            $rows[] = [
                'reviewable_id' => $productId,
                'reviewable_type' => Product::class,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('ak_reviews')->insert($rows);
    }
}
