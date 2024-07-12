<?php

namespace App\Console\Commands\Transform;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product;
use Backpack\Store\app\Models\Attribute;
use Backpack\Store\app\Models\AttributeValue;
use Backpack\Store\app\Models\AttributeProduct;

class NormalizePromAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:normalize-prom-attributes';

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
      // $this->removeWrongAttributes();
      // $this->moveSmallAttrs();
      // $this->moveLongCheckboxValues();

      $this->deleteAllNeeded();
      return 0;
    }
    
    private function deleteAllNeeded() {
      AttributeProduct::where('to_delete', 1)->delete();
      
      $avs = AttributeValue::where('to_delete', 1)->get();
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
     * removeWrongAttributes
     *
     * @return void
     */
    private function removeWrongAttributes() {
      $attrs = Attribute::where('name', 'like', '%Название%')->get();

      foreach($attrs as $attr) {
        AttributeProduct::where('attribute_id', $attr->id)->delete();
        AttributeValue::where('attribute_id', $attr->id)->delete();

        \DB::table('ak_attribute_category')->where('attribute_id', $attr->id)->delete();
        $attr->delete();
      }

    }

    /**
     * moveLongCheckboxValues
     *
     * @return void
     */
    private function moveLongCheckboxValues() {
      $avs = AttributeValue::get();

      foreach($avs as $av) {
        if(mb_strlen($av->value) > 50) {
          // $this->info(mb_strlen($av->value) . ' - ' . $av->value . ' - attribute = ' . $av->attribute_id);
          // continue;

          $attr = Attribute::find($av->attribute_id);
          $attr->in_properties = 1;
          $attr->in_filters = 0;
          $attr->type = 'string';
          $attr->save();

          $aps = AttributeProduct::where('attribute_id', $av->attribute_id)->get();

          foreach($aps as $ap) {
            $av = AttributeValue::find($ap->attribute_value_id);

            if(!$av) {
              $ap->delete();
              continue;
            }

            // move from checkbox to string
            $ap->attribute_value_id = null;
            $ap->value_trans = $av->value;
            $ap->save();

            // Delete checkbox value
            $av->to_delete = 1;
            $av->save();
          }
        }
      }
    }
        
    /**
     * moveSmallAttrs
     *
     * @return void
     */
    private function moveSmallAttrs() {
      $attrs = Attribute::get();

      foreach($attrs as $attr) {
        // store origin attribute type
        $origin_type = $attr->type;
        
        $aps = AttributeProduct::where('attribute_id', $attr->id)->get();

        // IF this attribute attached for a little numb of products
        if($aps->count() < 10) {
          
          if($origin_type === 'checkbox' || $origin_type === 'radio') {
            // Attributes with a few products amount transform to string type
            $attr->type = 'string';
          }

          // enable in properties
          $attr->in_properties = 1;
          // disable in filters
          $attr->in_filters = 0;
          $attr->save();

          // leave number attributes alone
          if($origin_type !== 'checkbox' && $origin_type !== 'radio') {
            continue;
          }


          // move values to value_trans field
          foreach($aps as $ap) {
            // find value form the list
            $value = AttributeValue::find($ap->attribute_value_id);

            if(!$value) {
              continue;
            }

            $ap->attribute_value_id = null;
            $ap->value_trans = $value->value;
            $ap->save();

            // Delete checkbox value
            $value->delete();
          }
        }
      }
    }

    
}
