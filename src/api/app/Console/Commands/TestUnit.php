<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Admin\Product;

class TestUnit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:unit';

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
      $product = Product::find(9999);
      dd($product->shouldBeSearchable());
    }

}