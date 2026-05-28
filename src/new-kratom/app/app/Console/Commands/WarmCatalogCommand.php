<?php

namespace App\Console\Commands;

use App\Services\CacheWarmer;
use Illuminate\Console\Command;

class WarmCatalogCommand extends Command
{
    protected $signature = 'catalog:warm {--only=} : products|taxonomies|all';

    protected $description = 'Прогреть Redis-проекции каталога из MySQL';

    public function handle(CacheWarmer $warmer): int
    {
        $only = $this->option('only') ?? 'all';

        match ($only) {
            'products' => $warmer->warmProducts(),
            'taxonomies' => $warmer->warmTaxonomies(),
            default => $warmer->warmAll(),
        };

        $this->info("Redis projection warmed ({$only}).");
        return self::SUCCESS;
    }
}
