<?php

namespace Database\Seeders;

use App\Services\CacheWarmer;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            TaxonomySeeder::class,
            CatalogSeeder::class,
            ReviewsSeeder::class,
        ]);

        // После seed-а сразу прогреваем Redis, чтобы фронт не упёрся
        // в пустую проекцию.
        app(CacheWarmer::class)->warmAll();
    }
}
