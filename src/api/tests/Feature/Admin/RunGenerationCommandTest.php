<?php

namespace Tests\Feature\Admin;

use App\Jobs\RunGenerationCommand;
use App\Models\GenerationRun;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Mockery;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class RunGenerationCommandTest extends TestCase
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

    public function test_job_retries_without_run_id_when_console_definition_is_stale(): void
    {
        $run = GenerationRun::query()->create([
            'type' => GenerationRun::TYPE_BOT_USERS,
            'status' => GenerationRun::STATUS_QUEUED,
            'command' => 'profile:generate-bots',
            'options' => ['count' => '3'],
        ]);

        $command = new SymfonyCommand('profile:generate-bots');
        $command->getDefinition()->addOption(new InputOption('run-id', null, InputOption::VALUE_OPTIONAL));

        Artisan::shouldReceive('all')
            ->once()
            ->andReturn([
                'profile:generate-bots' => $command,
            ]);

        Artisan::shouldReceive('call')
            ->once()
            ->withArgs(function (string $name, array $arguments, BufferedOutput $output) use ($run): bool {
                return $name === 'profile:generate-bots'
                    && ($arguments['--run-id'] ?? null) === $run->id;
            })
            ->andThrow(new InvalidOptionException('The "--run-id" option does not exist.'));

        Artisan::shouldReceive('call')
            ->once()
            ->withArgs(function (string $name, array $arguments, BufferedOutput $output): bool {
                return $name === 'profile:generate-bots'
                    && ! array_key_exists('--run-id', $arguments);
            })
            ->andReturn(0);

        (new RunGenerationCommand($run->id))->handle();

        $run->refresh();

        $this->assertSame(GenerationRun::STATUS_COMPLETED, $run->status);
        $this->assertNull($run->error_message);
        $this->assertSame(0, $run->result['exit_code'] ?? null);
    }

    public function test_failed_handler_marks_running_run_as_failed(): void
    {
        $run = GenerationRun::query()->create([
            'type' => GenerationRun::TYPE_BOT_USERS,
            'status' => GenerationRun::STATUS_RUNNING,
            'command' => 'profile:generate-bots',
            'options' => ['count' => '3'],
            'started_at' => now()->subMinutes(5),
        ]);

        $job = new RunGenerationCommand($run->id);
        $job->failed(new RuntimeException('The job timed out while executing.'));

        $run->refresh();

        $this->assertSame(GenerationRun::STATUS_FAILED, $run->status);
        $this->assertNotNull($run->finished_at);
        $this->assertSame('The job timed out while executing.', $run->error_message);
        $this->assertTrue((bool) ($run->result['timed_out'] ?? false));
    }
}
