<?php

namespace ParabellumKoval\Dumper\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ParabellumKoval\Dumper\Services\DumpManager;
use ParabellumKoval\Dumper\Services\RemoteDumpManager;

class SyncDumpToRemoteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 0;

    public function __construct(
        protected string $disk,
        protected string $path
    ) {
        $this->afterCommit();
    }

    public function handle(DumpManager $manager, RemoteDumpManager $remoteManager): void
    {
        $record = $manager->resolveStoredDump($this->disk, $this->path);

        if (!$record) {
            return;
        }

        $uploadedProviders = $remoteManager->upload($record);

        if ($uploadedProviders === []) {
            return;
        }

        $manager->markRemoteProviders($record, $uploadedProviders);
    }
}
