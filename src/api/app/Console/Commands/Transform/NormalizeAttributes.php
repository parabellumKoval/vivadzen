<?php

namespace App\Console\Commands\Transform;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product;
use Backpack\Store\app\Models\Attribute;
use Backpack\Store\app\Models\AttributeValue;
use Backpack\Store\app\Models\AttributeProduct;

ini_set('memory_limit', '500M');

class NormalizeAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:normalize-attributes {method?}';

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
      $method = $this->argument('method');

      if($method) {
        $this->{$method}();
      }else {
        // // 1) Remove bad (small, unused) site attributes
        // $this->attrsToRemove();

        // // 2) Join two simular site COUNTRY attribute
        // $this->joinSiteCountryAttribute();
        
        // //
        // $this->restoreDublicateCheckboxValues();


        // // Remove attributes that not attached to any product
        // $this->removeUnnecessaryValues();

        // // Attach attributes to categories
        // $this->attachAttributesToCategories();

        // $this->deleteAllNeeded();
      }
      
      return 0;
    }
            
    /**
     * attachAttributesToCategories
     *
     * @return void
     */
    private function attachAttributesToCategories() {
      // $products = Product::has('ap')->cursor();
      $aps = AttributeProduct::cursor();
      $aps_count = AttributeProduct::count();

      $bar = $this->output->createProgressBar($aps_count);
      $bar->start();

      foreach($aps as $ap) {
        $category = $ap->product->category;
        $attribute = $ap->attribute;
        
        if(!$category || !$attribute) {
          continue;
        }
      
        $category->attributes()->attach($attribute->id);
        
        $bar->advance();
      }

      $bar->finish();
    }
        
    /**
     * deleteAllNeeded
     *
     * @return void
     */
    private function deleteAllNeeded() {
      AttributeProduct::where('to_delete', 1)->delete();
      
      $avs = AttributeValue::where('to_delete', 1)->orWhere('to_delete_2', 1)->get();
      
      foreach($avs as $av) {
        AttributeProduct::where('attribute_value_id', $av->id)->delete();
        $av->delete();
      }

      $attrs = Attribute::where('to_delete', 1)->get();
      foreach($attrs as $attr) {
        AttributeProduct::where('attribute_id', $attr->id)->delete();
        AttributeValue::where('attribute_id', $attr->id)->delete();
        $attr->delete();
      }
    }
        
    /**
     * removeUnnecessaryValues
     *
     * @return void
     */
    private function removeUnnecessaryValues() {
      $avs = AttributeValue::all();
      
      foreach($avs as $av) {
        $aps_count = AttributeProduct::where('attribute_value_id', $av->id)->count();

        if(!$aps_count) {
          $av->to_delete_2 = 1;
          $av->save();
        }
      }
    }
    /**
     * restoreDublicateCheckboxValues
     *
     * @return void
     */
    private function restoreDublicateCheckboxValues() {
      $aps = AttributeProduct::whereNotNull('old_av_id')->get();

      foreach($aps as $ap) {
        //  :)))
        if(!$ap->old_av_id) {
          continue;
        }

        $ap->attribute_value_id = $ap->old_av_id;
        $ap->old_av_id = null;
        $ap->save();
      }
      
      AttributeValue::where('to_delete', 1)->update([
        'to_delete' => 0
      ]);
      
    }


    
    /**
     * attrsToRemove
     *
     * @return void
     */
    private function attrsToRemove() {
      
      $attr_ids = [
        2, // Brend
        9, // Страна поставщик
        6, // Капсулы
        16, // Цель
        21, // Количество игл
        22, // Длина
        23, // Ширина
        24, // Область применения
      ];

      foreach($attr_ids as $id) {
  
        AttributeProduct::where('attribute_id', $id)->update([
          'to_delete' => 1
        ]);

        AttributeValue::where('attribute_id', $id)->update([
          'to_delete' => 1
        ]);

        $attr = Attribute::find($id);
        
        if(!$attr)
          continue;

        $attr->to_delete = 1;
        $attr->save();

      }


    }
    
    /**
     * joinSiteCountryAttribute
     *
     * @return void
     */
    private function joinSiteCountryAttribute() {
      // Страна производитель
      $to_id = 3;    
      // Страна производства  
      $from_id = 8;

      // all FROM values
      $avs = AttributeValue::where('attribute_id', $from_id)->get();

      foreach($avs as $av) {
        // try find FROM value in TO attribute
        $new_av = AttributeValue::
                    where(\DB::raw('lower(old_value)'), mb_strtolower($av->value))
                    // where('value', 'like', '%' . $av->value . '%')
                  ->where('attribute_id', $to_id)
                  ->first();
        
        // if not found
        if(!$new_av) {
          // Switch this value to new attribute
          $av->attribute_id = $to_id;
          $av->save();
        }else {
          // delete if already exists
          $av->to_delete = 1;
          $av->save();
        }

        // Update all value attr product relations
        $aps = AttributeProduct::
                  where('attribute_value_id', $av->id)
                ->update([
                  // set new attribute id
                  'attribute_id' => $to_id,
                ]);
      }

      // need to delete old attr
      $attr = Attribute::find($from_id);
      $attr->to_delete = 1;
      $attr->save();
    }

}
