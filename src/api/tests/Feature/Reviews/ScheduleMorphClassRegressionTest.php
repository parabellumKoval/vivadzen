<?php

namespace Tests\Feature\Reviews;

use Backpack\Reviews\app\Models\Admin\Review as AdminReview;
use Backpack\Reviews\app\Models\Review;
use Backpack\Schedule\Services\ScheduleService;
use Backpack\Schedule\Traits\HasScheduleFields;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ScheduleMorphClassRegressionTest extends TestCase
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

    public function test_schedule_service_reuses_pending_publication_for_admin_review_model(): void
    {
        $review = $this->createReview();
        $service = app(ScheduleService::class);
        $firstPublication = $service->schedule($review, now()->addDay()->setTime(10, 0, 0), true);

        $adminReview = AdminReview::query()->findOrFail($review->id);
        $resolvedPublication = $service->getScheduled($adminReview);

        $this->assertNotNull($resolvedPublication);
        $this->assertTrue($firstPublication->is($resolvedPublication));

        $service->schedule($adminReview, now()->addDays(2)->setTime(12, 0, 0), true);

        $this->assertSame(1, \DB::table('ak_scheduled_publications')->count());
        $this->assertSame(1, $service->cancel($adminReview));
    }

    public function test_schedule_fields_read_existing_publication_for_admin_review_model(): void
    {
        $review = $this->createReview();
        $publishAt = now()->addDay()->setTime(11, 30, 0);
        app(ScheduleService::class)->schedule($review, $publishAt, true);

        $adminReview = AdminReview::query()->findOrFail($review->id);
        $crud = new class($adminReview) {
            public array $fields = [];

            public function __construct(private readonly AdminReview $entry)
            {
            }

            public function getCurrentEntry(): AdminReview
            {
                return $this->entry;
            }

            public function getModel(): string
            {
                return AdminReview::class;
            }

            public function addField(array $field): void
            {
                $this->fields[] = $field;
            }
        };

        $controller = new class($crud) {
            use HasScheduleFields;

            public function __construct(public object $crud)
            {
            }

            public function fields(): array
            {
                $this->addScheduleFields(['tab' => 'Таймер']);

                return $this->crud->fields;
            }
        };

        $fields = collect($controller->fields())->keyBy('name');

        $this->assertTrue((bool) ($fields['schedule_enabled']['default'] ?? false));
        $this->assertSame(
            $publishAt->format('Y-m-d H:i:s'),
            $fields['schedule_publish_at']['default'] ?? null
        );
        $this->assertArrayHasKey('schedule_info', $fields->all());
        $this->assertArrayHasKey('schedule_cancel', $fields->all());
    }

    protected function createReview(): Review
    {
        return Review::query()->create([
            'text' => 'Pending bot review',
            'is_moderated' => false,
            'rating' => 5,
            'owner_id' => 1,
            'parent_id' => 0,
            'extras' => ['generated_by_bot' => true],
            'reviewable_type' => 'App\\Models\\Product',
            'reviewable_id' => 101,
        ]);
    }
}
