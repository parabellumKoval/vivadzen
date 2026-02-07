<?php

namespace App\Console\Commands;

use App\Models\NpSettlement;
use App\Models\NpWarehouse;
use App\Services\NovaPoshta\NovaPoshtaClient;
use Illuminate\Console\Command;
use Meilisearch\Client as MeiliClient;

class NovaPoshtaSync extends Command
{
    protected $signature = 'novaposhta:sync
                            {--flush : Clear Meilisearch indexes before reindex}
                            {--mode=incremental : incremental|full (full truncates DB before sync)}
                            {--chunk=1000 : Chunk size for reindexing}
                            {--sleep-ms=250 : Delay between API requests in milliseconds}
                            {--retry=5 : Retry count on rate limit}
                            {--retry-sleep-ms=1000 : Base retry delay in milliseconds}
                            {--only-settlements : Sync settlements only}
                            {--only-warehouses : Sync warehouses only}
                            {--settlements-limit= : Page size for settlements sync}
                            {--warehouses-limit= : Page size for warehouses sync}';

    protected $description = 'Sync Nova Poshta settlements and warehouses into DB and Meilisearch';

    public function handle(): int
    {
        $this->configureScout();

        $client = app(NovaPoshtaClient::class);

        $mode = (string) $this->option('mode');
        if (!in_array($mode, ['incremental', 'full'], true)) {
            $this->warn("Unknown mode '{$mode}', falling back to incremental.");
            $mode = 'incremental';
        }

        if ($mode === 'full') {
            $this->warn('Full mode: truncating NP tables before sync.');
            NpSettlement::query()->truncate();
            NpWarehouse::query()->truncate();
        }

        $settlementsLimit = (int) ($this->option('settlements-limit') ?: config('novaposhta.sync.settlements_limit', 150));
        $warehousesLimit = (int) ($this->option('warehouses-limit') ?: config('novaposhta.sync.warehouses_limit', 200));

        $onlySettlements = (bool) $this->option('only-settlements');
        $onlyWarehouses = (bool) $this->option('only-warehouses');
        $syncSettlements = !$onlyWarehouses;
        $syncWarehouses = !$onlySettlements;

        if ($syncSettlements) {
            $this->info('Syncing settlements...');
            $this->syncSettlements($client, $settlementsLimit);
            $this->applyPopularRanking();
        }

        if ($syncWarehouses) {
            $this->info('Syncing warehouses...');
            $this->syncWarehouses($client, $warehousesLimit);
        }

        $this->applyMeiliSettings();
        $this->reindex((int) $this->option('chunk'), (bool) $this->option('flush'), $syncSettlements, $syncWarehouses);

        $this->info('Nova Poshta sync completed.');

        return 0;
    }

    private function syncSettlements(NovaPoshtaClient $client, int $limit): void
    {
        $page = 1;
        $total = 0;

        while (true) {
            $data = $this->fetchWithRetry(fn () => $client->fetchSettlements($page, $limit));
            if (empty($data)) {
                break;
            }

            $now = now();
            $rows = [];

            foreach ($data as $row) {
                $mapped = $this->mapSettlementRow($row, $now);
                if (!empty($mapped['ref'])) {
                    $rows[] = $mapped;
                }
            }

            if (!empty($rows)) {
                NpSettlement::upsert($rows, ['ref'], $this->settlementUpsertColumns());
                $total += count($rows);
            }

            $this->sleepBetweenRequests();
            $page++;
        }

        $this->line("Settlements synced: {$total}");
    }

    private function syncWarehouses(NovaPoshtaClient $client, int $limit): void
    {
        $page = 1;
        $total = 0;

        while (true) {
            $data = $this->fetchWithRetry(fn () => $client->fetchWarehouses($page, $limit));
            if (empty($data)) {
                break;
            }

            $now = now();
            $rows = [];

            foreach ($data as $row) {
                $mapped = $this->mapWarehouseRow($row, $now);
                if (!empty($mapped['ref'])) {
                    $rows[] = $mapped;
                }
            }

            if (!empty($rows)) {
                NpWarehouse::upsert($rows, ['ref'], $this->warehouseUpsertColumns());
                $total += count($rows);
            }

            $this->sleepBetweenRequests();
            $page++;
        }

        $this->line("Warehouses synced: {$total}");
    }

    private function mapSettlementRow(array $row, $now): array
    {
        return [
            'ref' => $row['Ref'] ?? null,
            'name_uk' => $row['Description'] ?? null,
            'name_ru' => $row['DescriptionRu'] ?? null,
            'area_uk' => $row['AreaDescription'] ?? null,
            'area_ru' => $row['AreaDescriptionRu'] ?? null,
            'region_uk' => $row['RegionDescription'] ?? ($row['RegionsDescription'] ?? null),
            'region_ru' => $row['RegionDescriptionRu'] ?? ($row['RegionsDescriptionRu'] ?? null),
            'type_uk' => $row['SettlementTypeDescription'] ?? null,
            'type_ru' => $row['SettlementTypeDescriptionRu'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function mapWarehouseRow(array $row, $now): array
    {
        $category = (string) ($row['CategoryOfWarehouse'] ?? '');
        $isPostomat = stripos($category, 'postomat') !== false;

        return [
            'ref' => $row['Ref'] ?? null,
            'settlement_ref' => $row['SettlementRef'] ?? null,
            'name_uk' => $row['Description'] ?? null,
            'name_ru' => $row['DescriptionRu'] ?? null,
            'category' => $category !== '' ? $category : null,
            'type' => $row['TypeOfWarehouse'] ?? null,
            'is_postomat' => $isPostomat,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function settlementUpsertColumns(): array
    {
        return [
            'name_uk',
            'name_ru',
            'area_uk',
            'area_ru',
            'region_uk',
            'region_ru',
            'type_uk',
            'type_ru',
            'updated_at',
        ];
    }

    private function warehouseUpsertColumns(): array
    {
        return [
            'settlement_ref',
            'name_uk',
            'name_ru',
            'category',
            'type',
            'is_postomat',
            'updated_at',
        ];
    }

    private function applyPopularRanking(): void
    {
        $popular = (array) config('novaposhta.popular_settlements', []);
        if (empty($popular)) {
            return;
        }

        NpSettlement::query()->update(['popular_rank' => null]);

        $rank = 1;
        foreach ($popular as $entry) {
            $updated = NpSettlement::query()
                ->where(function ($builder) use ($entry) {
                    if (!empty($entry['uk'])) {
                        $builder->orWhere('name_uk', $entry['uk']);
                    }
                    if (!empty($entry['ru'])) {
                        $builder->orWhere('name_ru', $entry['ru']);
                    }
                })
                ->update(['popular_rank' => $rank]);

            if ($updated === 0) {
                $label = $entry['uk'] ?? ($entry['ru'] ?? 'unknown');
                $this->warn("Popular settlement not found: {$label}");
            }

            $rank++;
        }
    }

    private function applyMeiliSettings(): void
    {
        $host = config('dress.search.meilisearch.host', 'http://meilisearch:7700');
        $key = config('dress.search.meilisearch.key');

        $client = new MeiliClient($host, $key);
        $settlementsIndex = config('novaposhta.indexes.settlements', 'np_settlements');
        $warehousesIndex = config('novaposhta.indexes.warehouses', 'np_warehouses');

        $this->ensureIndex($client, $settlementsIndex);
        $this->ensureIndex($client, $warehousesIndex);

        $settlementSettings = [
            'searchableAttributes' => [
                'name_uk',
                'name_ru',
                'area_uk',
                'area_ru',
                'region_uk',
                'region_ru',
                'type_uk',
                'type_ru',
            ],
            'filterableAttributes' => [
                'ref',
                'popular_rank',
            ],
            'sortableAttributes' => [
                'popular_rank',
            ],
            'typoTolerance' => [
                'enabled' => true,
                'minWordSizeForTypos' => ['oneTypo' => 4, 'twoTypos' => 7],
            ],
        ];

        $warehouseSettings = [
            'searchableAttributes' => [
                'name_uk',
                'name_ru',
            ],
            'filterableAttributes' => [
                'settlement_ref',
                'category',
                'is_postomat',
            ],
            'typoTolerance' => [
                'enabled' => true,
                'minWordSizeForTypos' => ['oneTypo' => 4, 'twoTypos' => 7],
            ],
        ];

        $tasks = [];
        $tasks[] = [$settlementsIndex, $client->index($settlementsIndex)->updateSettings($settlementSettings)['taskUid'] ?? null];
        $tasks[] = [$warehousesIndex, $client->index($warehousesIndex)->updateSettings($warehouseSettings)['taskUid'] ?? null];

        foreach ($tasks as [$uid, $taskUid]) {
            if ($taskUid === null) {
                continue;
            }
            $client->index($uid)->waitForTask($taskUid, 15000, 100);
        }
    }

    private function ensureIndex(MeiliClient $client, string $uid): void
    {
        try {
            $client->getIndex($uid);
        } catch (\Throwable $e) {
            $client->createIndex($uid, ['primaryKey' => 'id']);
        }
    }

    private function reindex(int $chunk, bool $flush, bool $syncSettlements, bool $syncWarehouses): void
    {
        $chunk = $chunk > 0 ? $chunk : 1000;

        if ($flush) {
            $this->info('Clearing NP indexes...');
            try {
                if ($syncSettlements) {
                    NpSettlement::query()->unsearchable();
                }
            } catch (\Throwable $e) {
                $this->warn('Failed to clear settlements index: '.$e->getMessage());
            }
            try {
                if ($syncWarehouses) {
                    NpWarehouse::query()->unsearchable();
                }
            } catch (\Throwable $e) {
                $this->warn('Failed to clear warehouses index: '.$e->getMessage());
            }
        }

        if ($syncSettlements) {
            $this->info('Reindexing settlements...');
            NpSettlement::query()
                ->orderBy('id')
                ->chunkById($chunk, function ($rows) {
                    $rows->searchable();
                });
        }

        if ($syncWarehouses) {
            $this->info('Reindexing warehouses...');
            NpWarehouse::query()
                ->orderBy('id')
                ->chunkById($chunk, function ($rows) {
                    $rows->searchable();
                });
        }
    }

    private function configureScout(): void
    {
        config()->set('scout.driver', 'meilisearch');
        config()->set('scout.queue', false);
        config()->set('scout.meilisearch.host', config('dress.search.meilisearch.host', 'http://meilisearch:7700'));
        config()->set('scout.meilisearch.key', config('dress.search.meilisearch.key'));
    }

    private function fetchWithRetry(callable $fn): array
    {
        $retries = max(0, (int) $this->option('retry'));
        $baseDelayMs = max(0, (int) $this->option('retry-sleep-ms'));

        $attempt = 0;
        while (true) {
            try {
                return $fn();
            } catch (\RuntimeException $e) {
                $message = strtolower($e->getMessage());
                $isRateLimit = str_contains($message, 'too many requests') || str_contains($message, 'to many requests');

                if (!$isRateLimit || $attempt >= $retries) {
                    throw $e;
                }

                $sleepMs = $baseDelayMs * (2 ** $attempt);
                $this->warn("Rate limit hit. Retry {$attempt} in {$sleepMs}ms...");
                usleep($sleepMs * 1000);
                $attempt++;
            }
        }
    }

    private function sleepBetweenRequests(): void
    {
        $sleepMs = max(0, (int) $this->option('sleep-ms'));
        if ($sleepMs > 0) {
            usleep($sleepMs * 1000);
        }
    }
}
