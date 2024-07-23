<?php

namespace App\Console\Commands\Catalog\Xml;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

// use App\Models\Product;

use Backpack\Store\app\Models\Product;
use Backpack\Store\app\Models\Attribute;
use Backpack\Store\app\Models\AttributeValue;
use Backpack\Store\app\Models\AttributeProduct;

use Rap2hpoutre\FastExcel\FastExcel;

ini_set('memory_limit', '500M');


class PromAttrs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:xml:prom-attrs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';


    public $find = 0;
    public $not_find = 0;
    public $total = 0;

    private $uniq_attrs = [];

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
     * @return mixed
     */
    public function handle()
    {
      $this->clearAllFromProm();
      $this->storeAttributes();
    }

      
    /**
     * clearAllFromProm
     *
     * @return void
     */
    private function clearAllFromProm() {
      AttributeProduct::where('source', 'prom')->delete();
      Attribute::where('source', 'prom')->delete();
      AttributeValue::where('source', 'prom')->delete();

      \DB::table('ak_attribute_category')->where('source', 'prom')->delete();
    }
  

    /**
     * getAttributes
     *
     * @param  mixed $rows
     * @return void
     */
    private function getAttributes($rows) {
      $attrs = [];

      foreach($rows as $key => $name) {
        if(str_starts_with($key, 'Назва_Характеристики_')) {
          $index =  preg_replace('/[^0-9]/', '', $key);

          if(empty($name)) {
            continue;
          }

          $attrs[] = [
            'key' => $name,
            'si' => $rows['Одиниця_виміру_Характеристики_' . $index],
            'value' => $rows['Значення_Характеристики_' . $index]
          ];
        }
      }

      return $attrs;
    }
    
    /**
     * createOrUpdateAttributes
     *
     * @param  mixed $attrs
     * @return void
     */
    private function createOrUpdateAttributes($attrs) {
      // foreach($attrs as $attr) {
      //   Attribute::where('name', $attr['key'])
      // }
    }

    
    /**
     * fillUniqAttr
     *
     * @param  mixed $attrs
     * @return void
     */
    private function fillUniqAttr($attrs, $product_id) {
      // $this->info('PRODUCT ID = ' . $product_id . "\n");

      foreach($attrs as $attr) {
        if(!isset($this->uniq_attrs[$attr['key']])) {
          // $this->info('NEW ATTR = ' . $attr['key'] . "\n");

          $this->uniq_attrs[$attr['key']] = [
            'values' => array([
                'product_id' => $product_id,
                'value' => $attr['value'],
                'si' => $attr['si']
            ])
          ];

          if(!empty($attr['si'])) {
            $this->uniq_attrs[$attr['key']]['si'] = array(
              $attr['si'] => 1
            );
          }

        }else {
          $this->uniq_attrs[$attr['key']]['values'][] = [
            'product_id' => $product_id,
            'value' => $attr['value'],
            'si' => $attr['si']
          ];

          // Add si if not exist
          if(empty($attr['si'])) {
            continue;
          }

          if(!isset($this->uniq_attrs[$attr['key']]['si'][$attr['si']])) {
            $this->uniq_attrs[$attr['key']]['si'][$attr['si']] = 1;
          }else {
            $this->uniq_attrs[$attr['key']]['si'][$attr['si']] += 1;
          }
        }
      }
    }
        
    /**
     * storeAttrsToFile
     *
     * @param  mixed $data
     * @return void
     */
    private function storeAttrsToFile($data){
      \File::put(public_path('prom_attrs_si.json'), json_encode($data));
    }
    
    /**
     * getAttrsFromFile
     *
     * @return void
     */
    private function getAttrsFromFile($filename) {
      $data = \File::get(public_path($filename));
      $attributes = json_decode($data, true);
      return $attributes;
    }
    
    
    /**
     * storeCheckboxValues
     *
     * @param  mixed $attr
     * @param  mixed $values_string
     * @return void
     */
    private function storeCheckboxValues($attr, $values_string) {

      $values_array = explode('|', $values_string);
      $avs = [];

      foreach($values_array as $v) {
        // check if values not exists yet
        if(!AttributeValue::where('value', 'like', '%' . $v . '%')->where('attribute_id', $attr->id)->first()) {
          $avs[] = $this->storeCheckboxValue($attr, $v);
        }
      }

      return $avs;
    }
    
    /**
     * storeCheckboxValue
     *
     * @param  mixed $attr
     * @param  mixed $value
     * @return void
     */
    private function storeCheckboxValue($attr, $value) {

      // CREATE ATTR VALUE
      $av = new AttributeValue;

      $av->value = $value;
      $av->attribute_id = $attr->id;
      $av->source = 'prom';
      $av->save();

      return $av->id;
    }

    
    /**
     * attachNumberToProduct
     *
     * @param  mixed $attr
     * @param  mixed $val
     * @return void
     */
    private function attachNumberToProduct($attr, $val) {
      $ap = new AttributeProduct;
      $ap->product_id = $val['product_id'];

      // si of the this value
      $val_si = $this->getClearSi($val['si']);

      // VALUE CORRECTION
      if(!empty($val_si) && $val_si !== $attr->getExtrasTrans('si')) {
        $true_value = $this->ask('Attribute ' . $attr->name . ' has si = ' . $attr->getExtrasTrans('si') . ' but value original si = ' . $val_si . ' and value = ' . $val['value'] . '. Please enter true value...');
        $ap->value = (double)$true_value;
      }else {
        //get only numbers from value
        $numeric_value = preg_replace('/[^0-9]/', '', $val['value']);
        $ap->value = (double)$numeric_value;
      }
      
      $ap->attribute_value_id = null;
      $ap->attribute_id  = $attr->id;
      $ap->source = 'prom';
      $ap->save();

      return $ap;
    }
    
    /**
     * attachCheckboxToProduct
     *
     * @param  mixed $attribute_value_id
     * @return void
     */
    private function attachCheckboxToProduct($attr, $product_id, $attribute_value_id) {
      $ap = new AttributeProduct;
      $ap->product_id = $product_id;
      $ap->value = null;
      $ap->attribute_value_id = $attribute_value_id;
      $ap->attribute_id  = $attr->id;
      $ap->source = 'prom';
      $ap->save();

      return $ap;
    }

    /**
     * storeValues
     *
     * @param  mixed $attr
     * @param  mixed $products
     * @return void
     */
    private function storeValues($attr, $products) {
      
      foreach($products as $val) {
        if($attr->type === 'checkbox') {
          $avs = $this->storeCheckboxValues($attr, $val['value']);

          foreach($avs as $av) {
            $this->attachCheckboxToProduct($attr, $val['product_id'], $av);
          }
        }else {
          $this->attachNumberToProduct($attr, $val);
        }

        // ATTR TO CATEGORY
        $this->attachAttrToCategory($attr->id, $val['product_id']);
      }
    }
    
    /**
     * attachAttrToCategory
     *
     * @param  mixed $product_id
     * @param  mixed $attr_id
     * @return void
     */
    private function attachAttrToCategory($attr_id, $product_id) {
      $product = Product::find($product_id);

      if(!$product) {
        return;
      }

      if(!$product->category) {
        return;
      }

      // Get product root category
      $root_category = $product->category->rootCategory;

      // If this attribute not attached to this category 
      if(!$root_category->attributes()->find($attr_id)) {
        $root_category->attributes()->attach($attr_id, ['source' => 'prom']);
      }
    }
    
    /**
     * storeAttributes
     *
     * @return void
     */
    private function storeAttributes() {
      $attrs = $this->getAttrsFromFile('prom_attrs_si.json');

      foreach($attrs as $name => $v) {
        $attr = new Attribute;
        $attr->name = $name;

        if(isset($v['si']) && !empty($v['si'])) {

          $si = [
            'name' => null,
            'amount' => null
          ];

          foreach($v['si'] as $si_name => $amount) {
            if($si['amount'] === null || $amount > $si['amount']) {
              $si['name'] = $this->getClearSi($si_name);
              $si['amount'] = $amount;
            }
          }

          $attr->type = 'number';
          $attr->setTranslation('extras_trans', 'ru', [
            'si' => $this->getClearSi($si['name'])
          ]);
        }else {
          $attr->type = 'checkbox';
        }

        $attr->source = 'prom';
        $attr->save();

        $this->storeValues($attr, $v['values']);
      }
    }
        
    /**
     * getClearSi
     *
     * @param  mixed $value
     * @return void
     */
    public function getClearSi($value) {
      if(empty($value)) {
        return null;
      }

      // only letters and %
      $clear = preg_replace('/[^\p{L}\p{N}\s\%]/u', '', $value);
      
      // $clear = preg_replace('/[^а-яА-Я]/', '', $value);
      return $clear;
    }
    /**
     * import
     *
     * @return void
     */
    public function import() {
      $out = new \Symfony\Component\Console\Output\ConsoleOutput();

      $file = public_path('/uploads/prom2.xlsx');

      $collection = (new FastExcel)->import($file, function ($line) use ($out) {
              
        $product = Product::where('import_id', $line['Унікальний_ідентифікатор'])->orWhere('code', $line['Код_товару'])->first();

        if($product) {
          $attributes = $this->getAttributes($line);
          $this->fillUniqAttr($attributes, $product->id);
        }


        return [
          'code' => $line['Код_товару'],
          'name' => $line['Назва_позиції'],
          'attrs' => null
        ];
      });


      $this->storeAttrsToFile($this->uniq_attrs);
      $this->info('_______FINISH_______');
      // \Log::info(print_r($this->uniq_attrs));
      // dd($this->uniq_attrs);
      // foreach ($collection as $value) {
      // }

      // dd($this->total, $this->find, $this->not_find);
      
    }



    /**
     * productsGenerator
     *
     * @return void
     */
    private function productsGenerator() {
      foreach (Product::cursor() as $user) {
          yield $user;
      }
    }
}
