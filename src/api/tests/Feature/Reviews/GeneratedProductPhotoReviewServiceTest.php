<?php

namespace Tests\Feature\Reviews;

use Backpack\Reviews\app\Models\GeneratedProductPhoto;
use Backpack\Reviews\app\Models\Review;
use Backpack\Reviews\app\Services\GeneratedProductPhotoReviewService;
use App\Models\Product;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GeneratedProductPhotoReviewServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('backpack-images.default_provider', 'local');
        config()->set('backpack-images.providers.local.disk', 'images');

        Storage::fake('images');

        Schema::dropIfExists('ak_generated_product_photos');
        Schema::dropIfExists('ak_products');
        Schema::dropIfExists('ak_reviews');

        Schema::create('ak_products', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('slug')->nullable();
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ak_reviews', function (Blueprint $table): void {
            $table->id();
            $table->integer('owner_id')->nullable();
            $table->boolean('is_moderated')->default(0);
            $table->text('text')->nullable();
            $table->json('extras')->nullable();
            $table->boolean('is_video')->default(0);
            $table->string('review_type')->nullable();
            $table->string('video_url')->nullable();
            $table->json('video_title')->nullable();
            $table->json('video_poster')->nullable();
            $table->json('photo_gallery')->nullable();
            $table->string('lang', 8)->nullable();
            $table->string('country', 2)->nullable();
            $table->integer('rating')->nullable();
            $table->integer('likes')->default(0);
            $table->integer('dislikes')->default(0);
            $table->integer('parent_id')->default(0)->nullable();
            $table->integer('lft')->default(0)->nullable();
            $table->integer('rgt')->default(0)->nullable();
            $table->integer('depth')->default(0)->nullable();
            $table->string('reviewable_type')->nullable();
            $table->unsignedBigInteger('reviewable_id')->nullable();
            $table->timestamps();
        });

        Schema::create('ak_generated_product_photos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->json('image')->nullable();
            $table->string('status', 32)->default('pending_review')->index();
            $table->longText('prompt')->nullable();
            $table->json('prompt_context')->nullable();
            $table->text('reference_image_url')->nullable();
            $table->string('reference_image_path')->nullable();
            $table->string('driver', 64)->nullable();
            $table->string('model', 128)->nullable();
            $table->unsignedBigInteger('generation_run_id')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('reviewed_by_id')->nullable()->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_first_approved_generated_photo_is_consumed_by_review(): void
    {
        DB::table('ak_products')->insert([
            [
                'id' => 1,
                'parent_id' => null,
                'slug' => 'base-product',
                'name' => json_encode(['ru' => 'Base product'], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'parent_id' => 1,
                'slug' => 'modification-product',
                'name' => json_encode(['ru' => 'Modification product'], JSON_UNESCAPED_UNICODE),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $baseProduct = Product::query()->without(['categories', 'ap', 'suppliers', 'parent'])->findOrFail(1);
        $modification = Product::query()->without(['categories', 'ap', 'suppliers', 'parent'])->findOrFail(2);

        $sourcePath = 'images/reviews/generated-product-photos/candidate.jpg';
        Storage::disk('images')->put('reviews/generated-product-photos/candidate.jpg', base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEBAQEA8QDw8PDw8QDw8PDw8PEA8QFREWFhURFRUYHSggGBolGxUVITEhJSkrLi4uFx8zODMsNygtLisBCgoKDg0OFQ8QFS0dFR0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAAEAAgMBIgACEQEDEQH/xAAXAAEBAQEAAAAAAAAAAAAAAAAAAQID/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEAMQAAAB6A//xAAVEAEBAAAAAAAAAAAAAAAAAAABEP/aAAgBAQABBQJf/8QAFBEBAAAAAAAAAAAAAAAAAAAAEP/aAAgBAwEBPwF//8QAFBEBAAAAAAAAAAAAAAAAAAAAEP/aAAgBAgEBPwF//8QAFBABAAAAAAAAAAAAAAAAAAAAEP/aAAgBAQAGPwJf/8QAFBABAAAAAAAAAAAAAAAAAAAAEP/aAAgBAQABPyF//9k='));

        $candidate = GeneratedProductPhoto::query()->create([
            'product_id' => $modification->id,
            'image' => [['src' => $sourcePath]],
            'status' => GeneratedProductPhoto::STATUS_APPROVED,
            'approved_at' => now(),
        ]);

        $review = Review::query()->create([
            'text' => 'Generated review with photo',
            'is_moderated' => false,
            'rating' => 5,
            'owner_id' => 15,
            'parent_id' => 0,
            'extras' => ['generated_by_bot' => true],
            'reviewable_type' => 'App\\Models\\Product',
            'reviewable_id' => $baseProduct->id,
        ]);

        $attached = app(GeneratedProductPhotoReviewService::class)->attachFirstApprovedPhoto($review, $baseProduct);

        $this->assertTrue($attached);

        $review->refresh();

        $this->assertSame('photo', $review->review_type);
        $this->assertNotEmpty($review->photo_gallery);

        $newPath = $review->photo_gallery[0]['src'] ?? null;

        $this->assertIsString($newPath);
        $this->assertStringContainsString('reviews/photos/', $newPath);
        Storage::disk('images')->assertExists($newPath);
        Storage::disk('images')->assertMissing('reviews/generated-product-photos/candidate.jpg');
        $this->assertFalse(GeneratedProductPhoto::query()->whereKey($candidate->id)->exists());
    }
}
