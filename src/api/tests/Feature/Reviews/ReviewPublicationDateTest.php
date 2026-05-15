<?php

namespace Tests\Feature\Reviews;

use Backpack\Reviews\app\Models\Review;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReviewPublicationDateTest extends TestCase
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

    public function test_review_publication_date_can_be_mass_assigned(): void
    {
        $publishedAt = Carbon::parse('2026-05-05 14:30:00');

        $review = Review::query()->create([
            'text' => 'Review with a custom publication date',
            'is_moderated' => true,
            'created_at' => $publishedAt->toDateTimeString(),
        ]);

        $this->assertTrue($publishedAt->equalTo($review->refresh()->created_at));
    }
}
