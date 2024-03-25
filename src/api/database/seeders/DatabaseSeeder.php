<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Backpack\Profile\database\seeders\ProfileSeeder;
use Backpack\Store\database\seeders\StoreSeeder;
use Backpack\Reviews\database\seeders\ReviewSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
      (new ProfileSeeder())->run();
      (new StoreSeeder())->run();
      (new ReviewSeeder())->run();
    }
}
