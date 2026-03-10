<?php

namespace Tests\Feature\Reviews;

use Backpack\Reviews\app\Events\ReviewPublished;
use Backpack\Reviews\app\Models\Review;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ReviewPublicationObserverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_unpublished_review_does_not_dispatch_published_event_on_create(): void
    {
        Event::fake([ReviewPublished::class]);

        Review::query()->create([
            'text' => 'Pending bot review',
            'is_moderated' => false,
            'rating' => 5,
            'owner_id' => null,
            'parent_id' => 0,
            'extras' => [
                'generated_by_bot' => true,
            ],
        ]);

        Event::assertNotDispatched(ReviewPublished::class);
    }

    public function test_moderated_review_dispatches_published_event_on_create(): void
    {
        Event::fake([ReviewPublished::class]);

        $review = Review::query()->create([
            'text' => 'Visible review',
            'is_moderated' => true,
            'rating' => 5,
            'owner_id' => null,
            'parent_id' => 0,
            'extras' => [],
        ]);

        Event::assertDispatched(ReviewPublished::class, fn (ReviewPublished $event) => $event->review->is($review));
    }
}
