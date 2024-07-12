<?php

namespace App\Console\Commands\Db;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product;
use Backpack\Store\app\Models\Attribute;
use Backpack\Store\app\Models\AttributeProduct;
use Backpack\Store\app\Models\AttributeValue;
use Backpack\Store\app\Models\Brand;

class CopyBrandFromProperty extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db-copy:brand-from-property';

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

      $attributes = Attribute::where('old_name', 'Бренд')->orWhere('old_name', 'Brend')->get();

      if(!$attributes)
        return;

      $page = 0;
      $per_page = 500;

      $total_count = AttributeProduct::whereIn('attribute_id', $attributes->pluck('id'))
        ->count();

      $this->line('total = ' . $total_count);

      do{
        $this->line('page = ' . $page + 1);
        $skip = $page * $per_page;
        $brands_ap = AttributeProduct::whereIn('attribute_id', $attributes->pluck('id'))->skip($skip)->take($per_page)->get();
        $this->brandFromProperty($brands_ap);
        $page += 1;
      }while($brands_ap->count());

      return 0;
    }

    public function brandFromProperty($brands_ap) {


      $bar = $this->output->createProgressBar($brands_ap->count());
      $bar->start();

      foreach($brands_ap as $ap) {
        sleep(0.5);
        $value = $ap->attribute_value;
        $product = $ap->product;

        if(!$value) {
          $this->line('no values');
          continue;
        }

        $brand = Brand::where(\DB::raw('lower(name)'), 'like', '%' . mb_strtolower(trim($value->old_value)) . '%')->first();

        if(!$brand) {
          sleep(0.5);
          $this->line('no brand = ' . $value->old_value);
          
          $brand = new Brand();
          $brand->setTranslation('name', 'ru', $value->old_value);
          $brand->slug = null;
          $brand->extras = [
            'is_popular' => 0
          ];
          $brand->save();
        }
        
        if(!$product) {
          $this->line('no product');
          continue;
        }

        $product->brand_id = $brand->id;
        $product->save();

        $bar->advance();
      }

      $bar->finish();

    }
}
