<?php

namespace App\Console\Commands\Transform;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product;
use Backpack\Store\app\Models\Supplier;
use Backpack\Store\app\Models\SupplierProduct;

class TransformProductsToSuppliers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transform:products-to-suppliers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'We process every product. We are changing the old structure in which the fields are code, 
    availability, price, etc. were directly in the product card, on the structure with Suppliers and the SupplierProduct table';

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
      $this->transformProducts();
    }

    private function transformProducts() {
      $products = Product::where('id', '>', 0);
      $products_cursor = $products->cursor();
      $products_count = $products->count();

      $bar = $this->output->createProgressBar($products_count);
      $bar->start();

      foreach($products_cursor as $product) {

        switch($product->parsed_from){
          case 'dobavki.ua':
            $supplier_name = 'Dobavki';
            break;
          case 'proteinplus.pro':
            $supplier_name = 'Proteinplus';
            break;
          case 'belok.ua':
            $supplier_name = 'Belok';
            break;
          default:
            $supplier_name = 'Склад';
        }

        
        $supplier = Supplier::where('name', $supplier_name)->first();

        $data = [
          'supplier_id' => $supplier->id,
          'product_id' => $product->id,
          'in_stock' => empty($product->in_stock)? 0: $product->in_stock,
          'price' => !empty($product->price)? $product->price: null,
          'old_price' => !empty($product->old_price)? $product->old_price: null,
        ];

        if($supplier_name === 'Proteinplus') {
          $data['barcode'] = !empty($product->code)? $product->code: null;
        }else {
          $data['code'] = !empty($product->code)? $product->code: null;
        }

        SupplierProduct::create($data);

        $bar->advance();
      }

      $bar->finish();
      
    }

}
