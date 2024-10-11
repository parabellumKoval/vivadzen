<?php

namespace App\Console\Commands\Transform;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Brand;

class SetBrandLangName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transform:brand-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      $this->transformNames();
      return 0;
    }

    private function transformNames() {
      $brands = Brand::all();

      $bar = $this->output->createProgressBar($brands->count());
      $bar->start();

      foreach($brands as $brand) {
        $ru_name = $brand->getTranslation('name', 'ru');

        $brand->setTranslation('name', 'uk', $ru_name);
        $brand->save();

        $bar->advance();
      }

      $bar->finish();
      
    }

}
