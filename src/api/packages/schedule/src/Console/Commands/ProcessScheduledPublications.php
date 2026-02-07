<?php

namespace Backpack\Schedule\Console\Commands;

use Backpack\Schedule\Models\ScheduledPublication;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ProcessScheduledPublications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:publish 
                            {--limit=100 : Максимальное количество записей для обработки}
                            {--dry-run : Показать что будет опубликовано без выполнения}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обработать запланированные публикации';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Проверяем, включен ли модуль
        if (!\Settings::get('backpack.schedule.enabled', true)) {
            $this->info('Модуль отложенных публикаций отключен');
            return self::SUCCESS;
        }

        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');

        $publications = ScheduledPublication::readyToPublish()
            ->limit($limit)
            ->get();

        if ($publications->isEmpty()) {
            $this->info('Нет записей для публикации');
            return self::SUCCESS;
        }

        $this->info("Найдено записей для публикации: {$publications->count()}");

        if ($dryRun) {
            $this->info('--- Режим dry-run ---');
        }

        $published = 0;
        $failed = 0;

        foreach ($publications as $publication) {
            $modelName = class_basename($publication->schedulable_type);
            $this->line("Обработка: {$modelName} #{$publication->schedulable_id}");

            if ($dryRun) {
                $this->info("  [DRY-RUN] Было бы опубликовано");
                continue;
            }

            try {
                if ($publication->publish()) {
                    $this->info("  ✓ Опубликовано");
                    $published++;
                } else {
                    $this->warn("  ✗ Не удалось опубликовать");
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Ошибка: {$e->getMessage()}");
                $failed++;
                
                // Помечаем как отмененную в случае критической ошибки
                $publication->update([
                    'status' => 'cancelled',
                    'metadata' => array_merge(
                        $publication->metadata ?? [],
                        ['error' => $e->getMessage(), 'failed_at' => Carbon::now()->toDateTimeString()]
                    ),
                ]);
            }
        }

        if (!$dryRun) {
            $this->newLine();
            $this->info("Результат: опубликовано {$published}, ошибок {$failed}");
        }

        return self::SUCCESS;
    }
}
