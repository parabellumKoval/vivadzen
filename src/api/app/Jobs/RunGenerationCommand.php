<?php

namespace App\Jobs;

use App\Models\GenerationRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use ParabellumKoval\AiContentGenerator\Services\ContentGenerator;
use ParabellumKoval\AiContentGenerator\Services\DriverRegistry;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Output\BufferedOutput;
use Throwable;

class RunGenerationCommand implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;
    public int $timeout = 1800;
    public bool $failOnTimeout = true;

    public function __construct(public int $runId)
    {
        $this->tries = max(1, (int) config('queue.generation_run.tries', 1));
        $this->timeout = max(60, (int) config('queue.generation_run.timeout_seconds', 1800));
    }

    public function handle(): void
    {
        $run = GenerationRun::query()->find($this->runId);

        if (! $run || in_array($run->status, [GenerationRun::STATUS_COMPLETED, GenerationRun::STATUS_FAILED], true)) {
            return;
        }

        $run->markRunning();
        $this->refreshRuntimeConfiguration();

        $output = new BufferedOutput();
        $arguments = $this->normalizeArguments($run);

        try {
            $exitCode = $this->callCommand($run->command, $arguments, $output);
            $capturedOutput = trim($output->fetch());
            $run->refresh();

            if ($exitCode !== 0) {
                $run->markFailed('Command finished with a non-zero exit code.', $capturedOutput, [
                    'exit_code' => $exitCode,
                ]);

                return;
            }

            $run->markCompleted($capturedOutput, [
                'exit_code' => $exitCode,
            ]);
        } catch (Throwable $exception) {
            $run->refresh();
            $run->markFailed($exception->getMessage(), trim($output->fetch()), [
                'exception' => get_class($exception),
            ]);

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $run = GenerationRun::query()->find($this->runId);
        if (! $run || in_array($run->status, [GenerationRun::STATUS_COMPLETED, GenerationRun::STATUS_FAILED], true)) {
            return;
        }

        $message = trim((string) ($exception?->getMessage() ?: 'Generation command execution failed.'));
        if ($message === '') {
            $message = 'Generation command execution failed.';
        }

        $result = [
            'exception' => $exception ? get_class($exception) : null,
            'timed_out' => $this->isTimeoutException($exception),
        ];

        $run->markFailed($message, $run->output, $result);
    }

    protected function normalizeArguments(GenerationRun $run): array
    {
        $arguments = $run->options ?? [];

        if ($this->commandSupportsOption($run->command, 'run-id')) {
            $arguments['--run-id'] = $run->getKey();
        }

        return $arguments;
    }

    protected function callCommand(string $command, array $arguments, BufferedOutput $output): int
    {
        try {
            return Artisan::call($command, $arguments, $output);
        } catch (InvalidOptionException $exception) {
            if (! $this->canRetryWithoutRunId($arguments, $exception)) {
                throw $exception;
            }

            unset($arguments['--run-id']);

            return Artisan::call($command, $arguments, $output);
        }
    }

    protected function refreshRuntimeConfiguration(): void
    {
        $this->invalidateSettingsCache();

        foreach ([ContentGenerator::class, DriverRegistry::class] as $abstract) {
            app()->forgetInstance($abstract);
        }
    }

    protected function invalidateSettingsCache(): void
    {
        try {
            $settings = app('backpack.settings');
        } catch (Throwable) {
            return;
        }

        if (! method_exists($settings, 'invalidate')) {
            return;
        }

        $keys = [
            'ai_content_generator',
            'ai_content_generator.default_driver',
            'ai_content_generator.providers',
        ];

        foreach (array_keys((array) config('ai-content-generator.drivers', [])) as $driver) {
            $prefix = "ai_content_generator.providers.{$driver}";
            $keys[] = $prefix;
            $keys[] = "{$prefix}.enabled";
            $keys[] = "{$prefix}.api_key";
            $keys[] = "{$prefix}.model";
        }

        foreach (array_values(array_unique($keys)) as $key) {
            try {
                $settings->invalidate($key);
            } catch (Throwable) {
                // A generation run should not fail just because cache invalidation is unavailable.
            }
        }
    }

    protected function canRetryWithoutRunId(array $arguments, InvalidOptionException $exception): bool
    {
        return array_key_exists('--run-id', $arguments)
            && str_contains($exception->getMessage(), '--run-id');
    }

    protected function commandSupportsOption(string $command, string $option): bool
    {
        try {
            $commands = Artisan::all();
            if (! isset($commands[$command])) {
                return false;
            }

            return $commands[$command]->getDefinition()->hasOption($option);
        } catch (Throwable) {
            return false;
        }
    }

    protected function isTimeoutException(?Throwable $exception): bool
    {
        if (! $exception) {
            return false;
        }

        $class = strtolower($exception::class);
        $message = strtolower($exception->getMessage());

        return str_contains($class, 'timeoutexceeded')
            || str_contains($class, 'timeout')
            || str_contains($message, 'timed out')
            || str_contains($message, 'timeout');
    }
}
