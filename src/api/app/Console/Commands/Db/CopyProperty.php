<?php

namespace App\Console\Commands\Db;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Category;
use Backpack\Store\app\Models\Product;
use Backpack\Store\app\Models\Attribute;
use Backpack\Store\app\Models\AttributeProduct;
use Backpack\Store\app\Models\AttributeValue;

class CopyProperty extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db-copy:property';

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
        // $this->line('Copy properties');
        // $this->copyProperties();

        // $this->line('Copy property values');
        // $this->copyPropertyValues();

        // $this->line('Attach property to categories');
        // $this->attachPropertyToCategories();

        // $this->line('Attach property to products');
        // $this->attachPropertyToProducts();

        $this->line('Remove dublicates');
        $this->removePropertuDublicates();

        return 0;
    }

    private function removePropertuDublicates() {
      $aps = AttributeProduct::all();

      $bar = $this->output->createProgressBar($aps->count());
      $bar->start();

      foreach($aps as $ap){
        $dublicates = AttributeProduct::
        // where('id', '!=', $ap->id)
          where('product_id', $ap->product_id)
          ->where('attribute_id', $ap->attribute_id)
          // ->where('attribute_value_id', $ap->attribute_value_id)
          ->orderBy('id')
          ->get();

        if($dublicates->count() > 1) {
          $correct = $dublicates->splice(-1, 1);
          AttributeProduct::destroy($dublicates->pluck('id'));
        }
        
        $bar->advance();
      }

      $bar->finish();
    }

    private function attachPropertyToProducts() {

      $pps = \DB::table('product_property')->select('product_property.*')->get();

      $bar = $this->output->createProgressBar(count($pps));
      $bar->start();

      foreach($pps as $pp){
        
        // find new product analogue
        $product = Product::where('old_id', $pp->product_id)->first();

        if(!$product) {
          continue;
        }

        // find new attribute analogue
        $attr = Attribute::whereJsonContains('extras->ids', $pp->property_id)->first();

        if(!$attr) {
          continue;
        }

        // Try find exists value
        $attr_value = AttributeValue::where('attribute_id', $attr->id)
                        ->where(\DB::raw('lower(old_value)'), 'like', '%' . strtolower($pp->value) . '%')
                        ->first();
        
        // Or Create new record
        if(!$attr_value) {
          $attr_value = new AttributeValue();
          $attr_value->attribute_id = $attr->id;
          $attr_value->old_value = $pp->value;
          $attr_value->setTranslation('value', 'ru', $pp->value);
          $attr_value->save();
        }

        // Check if combination already exists
        $ap = AttributeProduct::where('product_id', $product->id)
          ->where('attribute_id', $attr->id)
          ->where('attribute_value_id', $attr_value->id)
          ->first();

        if($ap)
          continue;

        // Attach value to product
        $ap = new AttributeProduct();
        $ap->product_id = $product->id;
        $ap->attribute_id = $attr->id;
        $ap->attribute_value_id = $attr_value->id;
        $ap->save();

        $bar->advance();
      }

      $bar->finish();
    }

    private function attachPropertyToCategories() {

      $cps = \DB::table('category_property')->select('category_property.*')->get();

      $bar = $this->output->createProgressBar(count($cps));
      $bar->start();

      foreach($cps as $cp){
        
        // find new category analogue
        $category = Category::where('old_id', $cp->category_id)->first();
      
        if(!$category) {
          continue;
        }

        // find new attribute analogue
        $attr = Attribute::whereJsonContains('extras->ids', $cp->property_id)->first();

        if(!$attr) {
          continue;
        }

        // Attach attributes to category
        $category->attributes()->attach($attr->id);
        
        $bar->advance();
      }

      $bar->finish();
    }

    private function copyPropertyValues() {

      $values = \DB::table('category_property_values')->select('category_property_values.*')->get();
  
      $bar = $this->output->createProgressBar(count($values));
      $bar->start();

      foreach($values as $value){

        // find new attribute analogue
        $attr = Attribute::whereJsonContains('extras->ids', $value->property_id)->first();

        if(!$attr) {
          continue;
        }

        $attr_value = AttributeValue::where('attribute_id', $attr->id)
                        ->where(\DB::raw('lower(old_value)'), 'like', '%' . strtolower($value->value) . '%')
                        ->first();
        
        // Skip If same value/attribute already exists
        if($attr_value) {
          continue;
        }

        // Add new otherwise
        $attr_value = new AttributeValue();
        $attr_value->attribute_id = $attr->id;
        $attr_value->old_value = $value->value;
        $attr_value->setTranslation('value', 'ru', $value->value);
        $attr_value->save();

        $bar->advance();
      }

      $bar->finish();
    }

    private function copyProperties() {

      $properties = \DB::table('properties')->select('properties.*')->get();
  
      $bar = $this->output->createProgressBar(count($properties));
      $bar->start();

      foreach($properties as $prop) {

        // Try to find isset attribute by name
        $attr = Attribute::where(\DB::raw('lower(old_name)'), 'like', '%' . strtolower($prop->name) . '%')->first();
        
        if(!$attr) {
          $attr = new Attribute();
        }

        $attr->slug = $prop->slug;
        $attr->type = 'radio';
        $attr->old_name = $prop->name;

        $attr->setTranslation('name', 'ru', $prop->name);

        if($prop->description) {
          $attr->setTranslation('content', 'ru', $prop->description);
        }
        
        // Push old id to array
        $ids_array = $attr->extras['ids'] ?? [];
        $ids_array[] = $prop->id;
        $attr->extras = [
          'ids' => $ids_array
        ];

        $attr->save();

        $bar->advance();
      }

      $bar->finish();
    }

}
