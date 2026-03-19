<?php

namespace Tests\Feature\Reviews;

use Backpack\Reviews\app\Models\Review;
use Backpack\Reviews\app\Services\GeneratedReviewScheduleService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GeneratedReviewScheduleServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('ak_scheduled_publications');
        Schema::dropIfExists('ak_reviews');

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

        Schema::create('ak_scheduled_publications', function (Blueprint $table): void {
            $table->id();
            $table->string('schedulable_type');
            $table->unsignedBigInteger('schedulable_id');
            $table->timestamp('publish_at');
            $table->boolean('overwrite_created_at')->default(false);
            $table->string('publish_field')->default('is_published');
            $table->string('publish_value')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('published_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function test_reviews_are_scheduled_within_maximum_number_of_days(): void
    {
        $reviews = collect(range(1, 10))->map(fn (int $index) => Review::query()->create([
                'text' => 'Generated review ' . $index,
                'is_moderated' => false,
                'rating' => 5,
                'owner_id' => $index,
                'parent_id' => 0,
                'extras' => ['generated_by_bot' => true],
                'reviewable_type' => 'App\\Models\\Product',
                'reviewable_id' => 100 + $index,
            ]));

        $scheduled = app(GeneratedReviewScheduleService::class)->schedule(
            collect($reviews),
            now()->addDay()->setTime(9, 0, 0),
            1,
            2,
            9,
            21
        );

        $this->assertSame(10, $scheduled);

        $rows = \DB::table('ak_scheduled_publications')->orderBy('publish_at')->get();

        $this->assertCount(10, $rows);
        $this->assertLessThanOrEqual(5, $rows->pluck('publish_at')->map(fn ($value) => substr((string) $value, 0, 10))->unique()->count());
        $this->assertTrue($rows->every(fn ($row) => $row->publish_field === 'is_moderated'));
        $this->assertTrue($rows->every(fn ($row) => $row->publish_value === '1'));
        $this->assertTrue($rows->every(fn ($row) => (int) $row->overwrite_created_at === 1));

        $countsPerDay = $rows
            ->groupBy(fn ($row) => substr((string) $row->publish_at, 0, 10))
            ->map->count();

        $this->assertTrue($countsPerDay->every(fn (int $count) => $count <= 2));
    }

    public function test_reviews_from_same_product_are_not_scheduled_back_to_back_when_alternatives_exist(): void
    {
        $reviews = collect();

        foreach (range(1, 5) as $productId) {
            foreach (range(1, 4) as $reviewIndex) {
                $reviews->push(Review::query()->create([
                    'text' => sprintf('Generated review %d-%d', $productId, $reviewIndex),
                    'is_moderated' => false,
                    'rating' => 5,
                    'owner_id' => ($productId * 100) + $reviewIndex,
                    'parent_id' => 0,
                    'extras' => ['generated_by_bot' => true],
                    'reviewable_type' => 'App\\Models\\Product',
                    'reviewable_id' => $productId,
                ]));
            }
        }

        $scheduled = app(GeneratedReviewScheduleService::class)->schedule(
            $reviews,
            now()->addDay()->setTime(9, 0, 0),
            1,
            2,
            9,
            21
        );

        $this->assertSame(20, $scheduled);

        $rows = \DB::table('ak_scheduled_publications')->orderBy('publish_at')->get();
        $reviewableIds = Review::query()
            ->whereIn('id', $rows->pluck('schedulable_id'))
            ->pluck('reviewable_id', 'id');

        $orderedProducts = $rows->map(fn ($row) => (int) $reviewableIds[(int) $row->schedulable_id])->values();

        foreach (range(1, $orderedProducts->count() - 1) as $index) {
            $this->assertNotSame($orderedProducts[$index - 1], $orderedProducts[$index]);
        }

        $firstTenProducts = $orderedProducts->take(10);

        $this->assertCount(5, $firstTenProducts->unique());
        $this->assertTrue(
            $firstTenProducts
                ->countBy()
                ->every(fn (int $count) => $count <= 2)
        );
    }
}
