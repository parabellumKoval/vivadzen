<?php

namespace Tests\Feature\Profile;

use Backpack\Profile\app\Models\Profile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use ParabellumKoval\AiContentGenerator\DTO\ResponseDto;
use ParabellumKoval\AiContentGenerator\Services\ContentGenerator;
use Tests\TestCase;

class GenerateBotUsersCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ak_profiles');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('ak_profiles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('sponsor_profile_id')->nullable();
            $table->string('referral_code', 64)->nullable();
            $table->string('role', 64)->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('locale', 8)->nullable();
            $table->string('timezone', 64)->nullable();
            $table->timestamp('birthdate')->nullable();
            $table->string('avatar_url')->nullable();
            $table->decimal('discount_percent', 8, 2)->nullable();
            $table->json('extras')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function test_command_retries_until_requested_bot_count_is_created(): void
    {
        $generator = Mockery::mock(ContentGenerator::class);
        $generator->shouldReceive('generate')
            ->twice()
            ->andReturn(
                new ResponseDto('fake', 'success', [[
                    'first_name' => 'Олена',
                    'last_name' => 'Бойко',
                    'display_name' => 'Олена Бойко',
                    'gender' => 'female',
                    'age' => 29,
                    'character' => 'Спокойная и внимательная к деталям.',
                    'literacy_level' => 8,
                    'speech_style' => 'спокойный, дружелюбный',
                    'emoji_usage' => 'редко, 0-1 эмодзи',
                    'punctuation_usage' => 'аккуратная пунктуация',
                    'message_length' => 'короткие сообщения',
                ]]),
                new ResponseDto('fake', 'success', [[
                    'first_name' => 'Ірина',
                    'last_name' => 'Мельник',
                    'display_name' => 'Ірина Мельник',
                    'gender' => 'female',
                    'age' => 35,
                    'character' => 'Прагматична, доброзичлива, без зайвого пафосу.',
                    'literacy_level' => 6,
                    'speech_style' => 'проста розмовна мова',
                    'emoji_usage' => 'майже без емодзі',
                    'punctuation_usage' => 'стримана пунктуація',
                    'message_length' => 'середні повідомлення',
                ]]),
            );

        $this->app->instance(ContentGenerator::class, $generator);

        $this->artisan('profile:generate-bots', [
            'count' => 2,
            '--batch' => 2,
            '--language' => ['ua'],
            '--country' => ['UA'],
            '--password' => 'secret-pass-123',
        ])->assertExitCode(0);

        $profiles = Profile::query()->where('role', 'bot')->orderBy('id')->get();

        $this->assertCount(2, $profiles);
        $this->assertSame(['uk'], $profiles->pluck('locale')->unique()->values()->all());
        $this->assertSame(['UA'], $profiles->pluck('country_code')->unique()->values()->all());
        $this->assertSame([8, 6], $profiles->map(fn (Profile $profile) => $profile->rolePayload('bot')['literacy_level'] ?? null)->all());
        $this->assertSame(
            ['короткие сообщения', 'середні повідомлення'],
            $profiles->map(fn (Profile $profile) => $profile->rolePayload('bot')['message_length'] ?? null)->all()
        );
    }
}
