<?php

namespace ParabellumKoval\Dumper\Console\Commands;

use Illuminate\Console\Command;
use ParabellumKoval\Dumper\Services\DumpManager;
use Throwable;

class RunAutoDumpCommand extends Command
{
    protected $signature = 'dumper:auto {case? : Case key defined in dumper.php}';

    protected $description = 'Run configured automatic dump cases';

    public function handle(DumpManager $manager): int
    {
        $case = $this->argument('case');

        if ($case) {
            return $this->runCase($manager, $case);
        }

        $cases = array_keys($manager->autoCases());

        if ($cases === []) {
            $this->warn('No auto cases configured.');

            return self::SUCCESS;
        }

        $exitCode = self::SUCCESS;

        foreach ($cases as $caseKey) {
            $exitCode = $this->runCase($manager, $caseKey) ?? $exitCode;
        }

        return $exitCode;
    }

    protected function runCase(DumpManager $manager, string $caseKey): int
    {
        try {
            $record = $manager->createAutoDump($caseKey);
        } catch (Throwable $exception) {
            report($exception);
            $this->error(sprintf('[%s] %s', $caseKey, $exception->getMessage()));

            return self::FAILURE;
        }

        if (!$record) {
            $this->warn(sprintf('Case `%s` is not configured.', $caseKey));

            return self::FAILURE;
        }

        $this->info(sprintf('[%s] Dump created: %s', $caseKey, $record->filename));

        return self::SUCCESS;
    }
}
