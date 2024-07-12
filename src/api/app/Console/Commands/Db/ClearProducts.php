<?php

namespace App\Console\Commands\Db;

use Illuminate\Console\Command;

// use Backpack\Store\app\Models\Product;

use Backpack\Store\app\Models\Product as NewProduct;

class ClearProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db-copy:clear-products';

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
      $this->clearProducts();
      // $this->clearOldProducts();
    }

    public function clearProducts() {
      $out = new \Symfony\Component\Console\Output\ConsoleOutput();
  
      // BY supplier_id
      $suppliers = [
        11 => 'Aur-ora',
        14 => 'Экстремал',
        21 => 'Biotus',
        23 => 'Powerful Progress',
        27 => 'slimboom',
        29 => 'Медок',
        33 => 'Медхауз',
        38 => 'Columbia'
      ];

      \DB::table('ak_products')
        ->select('ak_products.*')
        ->whereIn('supplier_id', array_keys($suppliers))
        ->delete();

      $out->writeln("All products from wrong suppliers were removed!");
    }

    public function clearOldProducts() {
      $out = new \Symfony\Component\Console\Output\ConsoleOutput();

      // SOFT DELETED
      \DB::table('products')
        ->select('products.*')
        ->whereNotNull('deleted_at')
        ->delete();
      
      $out->writeln("All soft deleted products were removed!");
  
      // BY supplier_id
      $suppliers = [
        11 => 'Aur-ora',
        14 => 'Экстремал',
        21 => 'Biotus',
        23 => 'Powerful Progress',
        27 => 'slimboom',
        29 => 'Медок',
        33 => 'Медхауз',
        38 => 'Columbia'
      ];

      \DB::table('products')
        ->select('products.*')
        ->whereIn('supplier_id', array_keys($suppliers))
        ->delete();

      $out->writeln("All products from wrong suppliers were removed!");

      //

      // \DB::table('products')
      //   ->select('products.*')
      //   ->where('updated_at', '<=', )
      //   ->delete();

      // $out->writeln("All products from wrong suppliers were removed!");
    }
}