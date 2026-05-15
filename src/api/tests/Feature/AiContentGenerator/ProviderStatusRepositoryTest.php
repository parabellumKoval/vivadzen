<?php

namespace Tests\Feature\AiContentGenerator;

use Backpack\Settings\Events\SettingsGroupChanged;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ParabellumKoval\AiContentGenerator\Models\AiProviderStatus;
use ParabellumKoval\AiContentGenerator\Services\ProviderStatusRepository;
use Tests\TestCase;

class ProviderStatusRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('ai_provider_statuses');
        Schema::create('ai_provider_statuses', function (Blueprint $table): void {
            $table->id();
            $table->string('driver')->unique();
            $table->string('status')->default('available');
            $table->string('error_code')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->timestamp('blocked_until')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function test_assert_available_auto_recovers_stale_error_status_without_blocked_until(): void
    {
        AiProviderStatus::query()->create([
            'driver' => 'openai',
            'status' => AiProviderStatus::STATUS_ERROR,
            'message' => 'cURL error 28',
            'error_code' => 'unexpected',
            'blocked_until' => null,
        ]);

        $repository = new ProviderStatusRepository();
        $status = $repository->assertAvailable('openai');

        $this->assertSame(AiProviderStatus::STATUS_AVAILABLE, $status->status);

        $freshStatus = AiProviderStatus::query()->where('driver', 'openai')->first();
        $this->assertNotNull($freshStatus);
        $this->assertSame(AiProviderStatus::STATUS_AVAILABLE, $freshStatus->status);
        $this->assertNull($freshStatus->blocked_until);
        $this->assertNull($freshStatus->message);
    }

    public function test_mark_error_sets_blocked_until_using_error_cooldown(): void
    {
        $repository = new ProviderStatusRepository();
        $repository->markError('openai', 'Network timeout', 'timeout');

        $status = AiProviderStatus::query()->where('driver', 'openai')->first();

        $this->assertNotNull($status);
        $this->assertSame(AiProviderStatus::STATUS_ERROR, $status->status);
        $this->assertSame('timeout', $status->error_code);
        $this->assertNotNull($status->blocked_until);
        $this->assertTrue($status->blocked_until->greaterThan(now()));
    }

    public function test_ai_content_settings_change_clears_provider_status(): void
    {
        AiProviderStatus::query()->create([
            'driver' => 'openai',
            'status' => AiProviderStatus::STATUS_INVALID_KEY,
            'message' => 'Invalid API key',
            'error_code' => 'invalid_key',
        ]);

        event(new SettingsGroupChanged('ai-content', [], [], [
            'ai_content_generator.providers.openai.api_key' => [
                'old' => 'old-key',
                'new' => 'new-key',
            ],
        ]));

        $status = AiProviderStatus::query()->where('driver', 'openai')->first();

        $this->assertNotNull($status);
        $this->assertSame(AiProviderStatus::STATUS_AVAILABLE, $status->status);
        $this->assertNull($status->error_code);
        $this->assertNull($status->message);
    }
}
