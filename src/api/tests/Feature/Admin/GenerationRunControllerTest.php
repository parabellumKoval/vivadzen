<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\GenerationRunController;
use App\Jobs\RunGenerationCommand;
use App\Models\GenerationRun;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GenerationRunControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('ak_generation_runs');
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

    public function test_profile_generation_endpoint_creates_bot_run(): void
    {
        Queue::fake();

        $response = app(GenerationRunController::class)->storeBots(Request::create('/admin/profile/generation-runs', 'POST', [
            'count' => 120,
            'batch' => 40,
            'languages' => ['uk', 'cs'],
            'countries' => ['UA', 'CZ'],
            'verified' => true,
            'dry_run' => true,
        ]));

        $this->assertSame(201, $response->getStatusCode());

        $run = GenerationRun::query()->latest('id')->first();

        $this->assertNotNull($run);
        $this->assertSame(GenerationRun::TYPE_BOT_USERS, $run->type);
        $this->assertSame('profile:generate-bots', $run->command);
        $this->assertSame(['uk', 'cs'], $run->options['--language']);
        $this->assertSame(['UA', 'CZ'], $run->options['--country']);
        $this->assertTrue($run->options['--dry-run']);

        Queue::assertPushed(RunGenerationCommand::class, fn (RunGenerationCommand $job) => $job->runId === $run->id);
    }

    public function test_review_generation_endpoint_builds_no_review_selection(): void
    {
        Queue::fake();

        $response = app(GenerationRunController::class)->storeReviews(Request::create('/admin/review/generation-runs', 'POST', [
            'selection_mode' => 'no_reviews',
            'product_limit' => 5,
            'min_reviews' => 2,
            'max_reviews' => 4,
            'locales' => ['cs'],
            'countries' => ['CZ'],
            'schedule_start' => 'tomorrow',
            'schedule_min_per_day' => 1,
            'schedule_max_per_day' => 2,
            'schedule_hour_from' => 10,
            'schedule_hour_to' => 18,
        ]));

        $this->assertSame(201, $response->getStatusCode());

        $run = GenerationRun::query()->latest('id')->first();

        $this->assertNotNull($run);
        $this->assertSame(GenerationRun::TYPE_PRODUCT_REVIEWS, $run->type);
        $this->assertSame('reviews:generate', $run->command);
        $this->assertTrue($run->options['--all']);
        $this->assertSame('0', $run->options['--review-count-max']);
        $this->assertSame('5', $run->options['--limit']);
        $this->assertSame(['cs'], $run->options['--locale']);
        $this->assertSame(['CZ'], $run->options['--country']);

        Queue::assertPushed(RunGenerationCommand::class, fn (RunGenerationCommand $job) => $job->runId === $run->id);
    }

    public function test_index_marks_stale_running_runs_as_failed(): void
    {
        $run = GenerationRun::query()->create([
            'type' => GenerationRun::TYPE_BOT_USERS,
            'status' => GenerationRun::STATUS_RUNNING,
            'command' => 'profile:generate-bots',
            'progress_total' => 20,
            'progress_current' => 0,
            'meta' => [
                'requested_count' => 20,
                'created_count' => 0,
            ],
            'started_at' => now()->subHours(2),
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);

        $response = app(GenerationRunController::class)->index(Request::create('/admin/profile/generation-runs', 'GET'));
        $this->assertSame(200, $response->getStatusCode());

        $run->refresh();
        $this->assertSame(GenerationRun::STATUS_FAILED, $run->status);
        $this->assertNotNull($run->finished_at);
        $this->assertNotNull($run->error_message);
        $this->assertTrue((bool) ($run->result['stale'] ?? false));
    }
}
