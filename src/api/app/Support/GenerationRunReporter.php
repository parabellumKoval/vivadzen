<?php

namespace App\Support;

use App\Models\GenerationRun;
use Illuminate\Support\Arr;

class GenerationRunReporter
{
    public function __construct(protected ?int $runId)
    {
    }

    public static function fromOption(mixed $runId): self
    {
        $normalized = is_numeric($runId) ? (int) $runId : null;

        return new self($normalized && $normalized > 0 ? $normalized : null);
    }

    public function enabled(): bool
    {
        return $this->runId !== null;
    }

    public function setTotal(int $total, array $meta = []): void
    {
        $this->update([
            'progress_total' => max(0, $total),
            'meta' => $this->mergeMeta($meta),
        ]);
    }

    public function setProgress(int $current, ?int $total = null, array $meta = []): void
    {
        $payload = [
            'progress_current' => max(0, $current),
            'meta' => $this->mergeMeta($meta),
        ];

        if ($total !== null) {
            $payload['progress_total'] = max(0, $total);
        }

        $this->update($payload);
    }

    public function merge(array $meta = [], ?array $result = null): void
    {
        $payload = [];

        if ($meta !== []) {
            $payload['meta'] = $this->mergeMeta($meta);
        }

        if ($result !== null) {
            $payload['result'] = $this->mergeResult($result);
        }

        if ($payload !== []) {
            $this->update($payload);
        }
    }

    protected function update(array $payload): void
    {
        if (! $this->enabled()) {
            return;
        }

        GenerationRun::query()->whereKey($this->runId)->update($payload);
    }

    protected function mergeMeta(array $meta): array
    {
        $run = $this->run();

        return array_replace_recursive($run?->meta ?? [], Arr::where($meta, fn ($value) => $value !== null));
    }

    protected function mergeResult(array $result): array
    {
        $run = $this->run();

        return array_replace_recursive($run?->result ?? [], Arr::where($result, fn ($value) => $value !== null));
    }

    protected function run(): ?GenerationRun
    {
        if (! $this->enabled()) {
            return null;
        }

        return GenerationRun::query()->find($this->runId);
    }
}
