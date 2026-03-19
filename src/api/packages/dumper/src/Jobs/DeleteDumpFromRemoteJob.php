<?php

namespace ParabellumKoval\Dumper\Jobs;

use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ParabellumKoval\Dumper\Data\DumpRecord;
use ParabellumKoval\Dumper\Services\RemoteDumpManager;

class DeleteDumpFromRemoteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 0;

    /**
     * @param array<int, string> $remoteProviders
     */
    public function __construct(
        protected string $disk,
        protected string $path,
        protected string $source,
        protected ?string $caseKey = null,
        protected array $remoteProviders = []
    ) {
        $this->afterCommit();
    }

    public function handle(RemoteDumpManager $remoteManager): void
    {
        $remoteManager->delete(new DumpRecord(
            $this->disk,
            $this->path,
            basename($this->path),
            CarbonImmutable::now(),
            ['*'],
            $this->source,
            $this->caseKey,
            null,
            1,
            $this->remoteProviders
        ));
    }
}
