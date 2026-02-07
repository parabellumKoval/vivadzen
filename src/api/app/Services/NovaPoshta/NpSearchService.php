<?php

namespace App\Services\NovaPoshta;

use App\Models\NpSettlement;
use App\Models\NpWarehouse;
use Illuminate\Database\Eloquent\Collection;

class NpSearchService
{
    public function searchSettlements(string $query, int $limit): Collection
    {
        $query = trim($query);

        if ($query === '') {
            return new Collection();
        }

        if ($this->scoutAvailable()) {
            $page = NpSettlement::search($query)->paginate($limit);
            return $page->getCollection();
        }

        return NpSettlement::query()
            ->where(function ($builder) use ($query) {
                $builder->where('name_uk', 'like', "%{$query}%")
                    ->orWhere('name_ru', 'like', "%{$query}%");
            })
            ->limit($limit)
            ->get();
    }

    public function searchWarehouses(string $query, string $settlementRef, int $limit): Collection
    {
        $query = trim($query);

        if ($this->scoutAvailable()) {
            $builder = NpWarehouse::search($query);
            $builder->where('settlement_ref', $settlementRef);
            $page = $builder->paginate($limit);
            return $page->getCollection();
        }

        $db = NpWarehouse::query()->where('settlement_ref', $settlementRef);
        if ($query !== '') {
            $db->where(function ($builder) use ($query) {
                $builder->where('name_uk', 'like', "%{$query}%")
                    ->orWhere('name_ru', 'like', "%{$query}%");
            });
        }

        return $db->limit($limit)->get();
    }

    public function popularSettlements(int $limit): Collection
    {
        return NpSettlement::query()
            ->whereNotNull('popular_rank')
            ->orderBy('popular_rank')
            ->limit($limit)
            ->get();
    }

    private function scoutAvailable(): bool
    {
        $this->configureScout();

        return class_exists(\Laravel\Scout\EngineManager::class)
            && config('scout.driver') === 'meilisearch';
    }

    private function configureScout(): void
    {
        if (config('scout.driver') === 'meilisearch') {
            return;
        }

        config()->set('scout.driver', 'meilisearch');
        config()->set('scout.queue', false);
        config()->set('scout.meilisearch.host', config('dress.search.meilisearch.host', 'http://meilisearch:7700'));
        config()->set('scout.meilisearch.key', config('dress.search.meilisearch.key'));
    }
}
