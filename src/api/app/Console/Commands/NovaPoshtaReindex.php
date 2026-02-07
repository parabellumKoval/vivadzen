<?php

namespace App\Console\Commands;

use App\Models\NpSettlement;
use App\Models\NpWarehouse;
use Illuminate\Console\Command;
use Meilisearch\Client as MeiliClient;

class NovaPoshtaReindex extends Command
{
    protected $signature = 'novaposhta:reindex
                            {--flush : Clear Meilisearch indexes before reindex}
                            {--chunk=1000 : Chunk size for reindexing}';

    protected $description = 'Reindex Nova Poshta settlements and warehouses into Meilisearch only';

    public function handle(): int
    {
        $this->configureScout();

        $this->applyMeiliSettings();

        $chunk = (int) $this->option('chunk');
        $flush = (bool) $this->option('flush');

        if ($flush) {
            $this->info('Clearing NP indexes...');
            try {
                NpSettlement::query()->unsearchable();
            } catch (\Throwable $e) {
                $this->warn('Failed to clear settlements index: '.$e->getMessage());
            }
            try {
                NpWarehouse::query()->unsearchable();
            } catch (\Throwable $e) {
                $this->warn('Failed to clear warehouses index: '.$e->getMessage());
            }
        }

        $chunk = $chunk > 0 ? $chunk : 1000;

        $this->info('Reindexing settlements...');
        NpSettlement::query()
            ->orderBy('id')
            ->chunkById($chunk, function ($rows) {
                $rows->searchable();
            });

        $this->info('Reindexing warehouses...');
        NpWarehouse::query()
            ->orderBy('id')
            ->chunkById($chunk, function ($rows) {
                $rows->searchable();
            });

        $this->info('Nova Poshta reindex completed.');

        return 0;
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

    private function configureScout(): void
    {
        config()->set('scout.driver', 'meilisearch');
        config()->set('scout.queue', false);
        config()->set('scout.meilisearch.host', config('dress.search.meilisearch.host', 'http://meilisearch:7700'));
        config()->set('scout.meilisearch.key', config('dress.search.meilisearch.key'));
    }
}
