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
            LabBatchesSeeder::class,
            ReviewsSeeder::class,
            CzechCitiesSeeder::class,
            DeliveryPaymentSeeder::class,
            UserDemoSeeder::class,
            ForumDemoSeeder::class,
            WikiCategoriesSeeder::class,
            WikiContentVedaSeeder::class,
            WikiContentHistorieSeeder::class,
            WikiContentLegislativaSeeder::class,
            WikiContentKvalitaSeeder::class,
        ]);

        // После seed-а сразу прогреваем Redis, чтобы фронт не упёрся
        // в пустую проекцию.
        app(CacheWarmer::class)->warmAll();
    }
}
