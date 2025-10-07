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
    protected $signature = 'db:normalize-prom-attributes {method?}';

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
        // 1) Delete unnecessary attributes
        $this->info('1) Delete unnecessary attributes');
        $this->removeWrongAttributes();
        
        // 2) Switch small checkbox attributes to string type 
        $this->info('2) Switch small checkbox attributes to string type');
        $this->moveSmallAttrs();

        // 3) Switch long checkbox attribute to string type
        $this->info('3) Switch long checkbox attribute to string type');
        $this->moveLongCheckboxValues();

        // 4) Merge simular PROM attributes to single attr
        $this->info('4) Merge simular PROM attributes to single attr');
        $this->mergePromAttrs();

        // 5) Join PROM attribute to existed SITE attribute
        $this->info('5) Join PROM attribute to existed SITE attribute');
        $this->joinAttrsAuto();

        // 6) Move site Volume attribute to multiple PROM attributes
        $this->info('6) Move site Volume attribute to multiple PROM attributes');
        $this->attrVolume();

        // 7) mergeDublicateCheckboxValues
        $this->info('7) mergeDublicateCheckboxValues');
        $this->mergeDublicateCheckboxValues();

        // 8) Remove dublicated rows from relations table AttributeProduct 
        $this->info('8) Remove dublicated rows from relations table AttributeProduct');   
        $this->removeDublicatesRelations();

        // 9) Move not popular attributes with small products amount from filters
        $this->info('9) Move not popular attributes with small products amount from filters');  
        $this->moveNotPopularFromFilters();
      }

      return 0;
    }
    
    
    /**
     * moveNotPopularFromFilters
     *
     * @return void
     */
    private function moveNotPopularFromFilters() {
      $attrs = Attribute::where('to_delete', 0)->get();

      foreach($attrs as $attr) {
        $aps_count = AttributeProduct::where('attribute_id', $attr->id)->count();

        if($aps_count === 0) 
        {
          $this->error($attr->name . ' has ' . $aps_count . 'products. Need to remove attribute'); 
          $this->removeAttribute($attr);
        }
        else if($aps_count < 100 || $attr->type === 'string') 
        {
          $this->info($attr->name . ' has ' . $aps_count . 'products. Need to remove from filters'); 
          
          $attr->in_filters = 0;
          $attr->in_properties = 1;
          $attr->save();
        }
      }

    }
    
    /**
     * joinAttrs2
     *
     * @return void
     */
    private function joinAttrsAuto() {

      $attr_names = [
        // Prom_attribute_name => Site_attribute_name
        'Цвет' => 'Цвет',
        'Материал' => 'Материал',
        'Вкус' => 'Вкус',
        'Тип кожи' => 'Тип кожи',
        'Назначение и результат' => 'Назначение',
        'Бренд' => 'Бренд',
        'Форма выпуска' => 'Форма выпуска'
      ];

      foreach($attr_names as $from_name => $to_name) {
        $attr_from = Attribute::
                        where('name->ru', $from_name)
                      ->where('source', 'prom')
                      ->first();
                      
        $attr_to = Attribute::
                        where('name->ru', $to_name)
                      ->where('source', 'site')
                      ->first();

        $this->info('prom id ' . $attr_from->id . ', site id ' . $attr_to->id);
        $this->joinPromToSiteAttrs($attr_to->id, $attr_from->id);
      }
      
    }

    /**
     * joinAttrs
     *
     * @return void
     */
    private function joinAttrs() {

      // Цвет
      $color_site_id = 15;
      $color_prom_id = 27;
      $this->joinPromToSiteAttrs($color_site_id, $color_prom_id);

      // Материал
      $metterial_site_id = 7;
      $metterial_prom_id = 29;
      $this->joinPromToSiteAttrs($metterial_site_id, $metterial_prom_id);

      // Вкус
      $taste_site_id = 14;
      $taste_prom_id = 34;
      $this->joinPromToSiteAttrs($taste_site_id, $taste_prom_id);

      // Тип кожи
      $taste_site_id = 11;
      $taste_prom_id = 55;
      $this->joinPromToSiteAttrs($taste_site_id, $taste_prom_id);

      // Назначение
      $taste_site_id = 20;
      $taste_prom_id = 50; // Назначение и результат
      $this->joinPromToSiteAttrs($taste_site_id, $taste_prom_id);

      // Бренд
      $taste_site_id = 12;
      $taste_prom_id = 25;
      $this->joinPromToSiteAttrs($taste_site_id, $taste_prom_id);

      // Форма выпуска
      $taste_site_id = 5;
      $taste_prom_id = 30;
      $this->joinPromToSiteAttrs($taste_site_id, $taste_prom_id);
    }
        
    /**
     * joinCheckboxAttr
     *
     * @return void
     */
    private function joinFromCheckboxAttr($attr_site_id, $attr_prom_id) {
      // For each PROM value
      $avs = AttributeValue::where('attribute_id', $attr_prom_id)->get();

      foreach($avs as $av) {
        // Find SITE value analogue
        $new_value = AttributeValue::
                      where('attribute_id', $attr_site_id)
                    ->where(\DB::raw('lower(old_value)'), mb_strtolower($av->value))
                    ->first();

        // if no analogue value
        if(!$new_value) {
          // switch this PROM attribute value to SITE attribute value
          $av->attribute_id = $attr_site_id;
          $av->save();
          $new_value_id = $av->id;
        }
        // if there is analogue value on the site already exists
        else {
          // delete old value
          $av->to_delete = 1;
          $av->save();
          $new_value_id = $new_value->id;
        }

        // Update product attributes relations
        $aps = AttributeProduct::
                  where('attribute_id', $attr_prom_id)
                ->where('attribute_value_id', $av->id)
                ->update([
                  'attribute_id' => $attr_site_id,
                  'attribute_value_id' => $new_value_id
                ]);
      }
    }
    
    /**
     * joinFromStringAttr
     *
     * @return void
     */
    private function joinFromStringAttr($attr_site_id, $attr_prom_id) {
      // All aps from PROM
      $aps = AttributeProduct::where('attribute_id', $attr_prom_id)->get();

      foreach($aps as $ap) {
        // try find checkbox value analogue already exists
        $av = AttributeValue::
                  where('attribute_id', $attr_site_id)
                ->where(\DB::raw('lower(old_value)'), mb_strtolower($ap->value))
                ->first();

        // if no exists create new checkbox value as SITE attribute
        if(!$av) {
          $av = AttributeValue::create([
            'value' => $ap->value,
            'attribute_id' => $attr_site_id
          ]);
        }

        // update relations from PROM attribute string type to SITE attribute checkbox type
        $ap->update([
          'attribute_id' => $attr_site_id,
          'attribute_value_id' => $av->id,
          'value' => null,
          'value_trans' => null
        ]);
      }
    }
    
    /**
     * joinPromToSiteAttrs
     *
     * @param  mixed $attr_site_id
     * @param  mixed $attr_prom_id
     * @return void
     */
    private function joinPromToSiteAttrs($attr_site_id, $attr_prom_id) {

      // PROM attribute
      $old_attr = Attribute::find($attr_prom_id);

      if($old_attr->type === 'string') 
      {
        $this->joinFromStringAttr($attr_site_id, $attr_prom_id);
      }
      else if($old_attr->type === 'radio' || $old_attr->type === 'checkbox') 
      {
        $this->joinFromCheckboxAttr($attr_site_id, $attr_prom_id);
      }
      
      // Set to delete PROM attribute
      $old_attr->to_delete = 1;
      $old_attr->save();
    }

    /**
     * mergePromAttrs
     *
     * @return void
     */
    private function mergePromAttrs() {
      
      // Вес, г
      $prom_weight_id = 40;
      // Масса, г
      $prom_mass_id = 228;

      $this->mergePromSimular($prom_mass_id, $prom_weight_id);

      // Колиечство, шт
      $prom_quant_id = 38;
      // Количество в упаковке, шт
      $prom_quant_pack_id = 31;

      $this->mergePromSimular($prom_quant_pack_id, $prom_quant_id);


      // Объем (number)
      $prom_volume_to = 26;
      // Объем (мл) (checkbox)
      $prom_volume_from = 42;

      $this->mergePromSimularCheckboxToNumber($prom_volume_from, $prom_volume_to);
    }
    
    /**
     * mergePromSimularCheckboxToNumber
     *
     * @param  mixed $from_id
     * @param  mixed $to_id
     * @return void
     */
    private function mergePromSimularCheckboxToNumber($from_id, $to_id) {
      $old_avs = AttributeValue::where('attribute_id', $from_id)->get();

      foreach($old_avs as $old_av) {

        $old_aps = AttributeProduct::
                      where('attribute_id', $from_id)
                    ->where('attribute_value_id', $old_av->id)
                    ->get();

        foreach($old_aps as $old_ap) {
          $old_ap->old_av_id = $old_ap->attribute_value_id;
          $old_ap->attribute_value_id = null;
          $old_ap->attribute_id = $to_id;
          $old_ap->value = $this->getClearNumberValue($old_av->value);
          $old_ap->save();
        }

        $old_av->to_delete = 1;
        $old_av->save();
      }

      $attr = Attribute::find($from_id);
      $attr->to_delete = 1;
      $attr->save();
    }
    
    /**
     * mergePromSimular
     *
     * @param  mixed $from_id
     * @param  mixed $to_id
     * @return void
     */
    private function mergePromSimular($from_id, $to_id) {
      $aps = AttributeProduct::where('attribute_id', $from_id)->get();

      foreach($aps as $ap) {
        $ap->attribute_id = $to_id;
        $ap->save();
      }

      $attr = Attribute::find($from_id);
      $attr->to_delete = 1;
      $attr->save();
    }
    
    /**
     * removeWrongAttributes
     *
     * @return void
     */
    private function removeWrongAttributes() {
      // THIS IS RIGHT
      $attrs = collect();

      // match Название and Название модификации
      $names = Attribute::where('name', 'like', '%Название%')->get();

      // match Артикул and Артикул модели
      $articuls = Attribute::where('name', 'like', '%Артикул%')->get();
      $prices = Attribute::where('name', 'like', '%Цена%')->get();

      $currency = Attribute::where('name', 'like', '%Валюта%')->get();
      $availb = Attribute::where('name', 'like', '%Наличие%')->get();
      $category = Attribute::where('name', 'like', '%Категория%')->get();
      $series = Attribute::where('name', 'like', '%Серия%')->get();

      // 
      $brand = Attribute::where('name', 'like', '%Бренд%')->get();


      $all_attrs = $attrs->merge($names)
                          ->merge($articuls)
                          ->merge($prices)
                          ->merge($currency)
                          ->merge($availb)
                          ->merge($category)
                          ->merge($series)
                          ->merge($brand);

      foreach($all_attrs as $attr) {
        $this->removeAttribute($attr);
      }
    }
    
    /**
     * removeAttribute
     *
     * @return void
     */
    private function removeAttribute($attr) {
      AttributeProduct::where('attribute_id', $attr->id)->update([
        'to_delete' => 1
      ]);

      AttributeValue::where('attribute_id', $attr->id)->update([
        'to_delete' => 1
      ]);

      // FORCE !!!
      \DB::table('ak_attribute_category')->where('attribute_id', $attr->id)->delete();

      $attr->to_delete = 1;
      $attr->save();

    }

    /**
     * moveLongCheckboxValues
     * 
     * Move long (unprocessed) checkbox values to string type attribute
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
     * Move checkbox/radio attributes with a few product amount to string attribute type
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

    

    /**
     * getClearNumberValue
     *
     * @param  mixed $value
     * @return void
     */
    public function getClearNumberValue($value) {
      if(empty($value)) {
        return null;
      }

      $clear = preg_replace('/[^0-9]/', '', $value);
      
      return (double)$clear;
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
      $clear = preg_replace('/[^\p{L}\%]/u', '', $value);
      
      // $clear = preg_replace('/[^а-яА-Я]/', '', $value);
      return $clear;
    }
      
    
    /**
     * toPromAttrQVW
     *
     * @param  mixed $old_av
     * @return void
     */
    private function toPromAttrQVW(AttributeValue $old_av) {

      $allowed_si = ['шт', 'гр', 'г', 'кг', 'мл', 'л'];

      $value= $this->getClearNumberValue($old_av->old_value);
      $si = $this->getClearSi($old_av->old_value);
      $is_denny_sybmols = preg_match('/[\*\-\+]/u', $old_av->old_value);

      // Skip
      if(!empty($si)) {
        if(!in_array($si, $allowed_si) || $is_denny_sybmols) {
          $old_av->to_delete = 1;
          $old_av->save();

          AttributeProduct::where('attribute_value_id', $old_av->id)->update([
            'to_delete' => 1
          ]);

          return;
        }
      }

      if(empty($si) || $si === 'шт') {
        // Колиечство, шт
        // $prom_attr_id = 840;
        $attr = Attribute::
            where('name->ru', 'Количество')
            ->where('source', 'prom')
            ->first();

        $prom_attr_id = $attr->id;
        $converted_value = $value;
      }else if(in_array($si, ['гр', 'г', 'кг'])) {
        // Вес, г
        // $prom_attr_id = 850;

        $attr = Attribute::
            where('name->ru', 'Вес')
            ->where('source', 'prom')
            ->first();

        $prom_attr_id = $attr->id;

        if($si === 'гр' || $si === 'г') {
          $converted_value = $value;
        }else if($si === 'кг') {
          $converted_value = $value * 1000;
        }
      }else if(in_array($si, ['мл', 'л'])) {
        // Объем, мл
        // $prom_attr_id = 828;

        $attr = Attribute::
            where('name->ru', 'Объем')
            ->where('source', 'prom')
            ->first();

        $prom_attr_id = $attr->id;

        if($si === 'мл') {
          $converted_value = $value;
        }else if($si === 'л') {
          $converted_value = $value * 1000;
        }
      }

      // for each Attribute Product
      AttributeProduct::where('attribute_value_id', $old_av->id)->update([
        'value' => $converted_value,
        'attribute_id' => $prom_attr_id,
        'attribute_value_id' => null
      ]);

      // need to delete
      $old_av->to_delete = 1;
      $old_av->save();
    }

    /**
     * attrVolume
     * 
     * Объем
     *
     * @return void
     */
    private function attrVolume() {
      // Volume id
      // $site_volume_id = 13;

      $attr_from = Attribute::
        where('old_name', 'Объём')
        ->where('source', 'site')
        ->first();

      // Get all SITE volume values
      $values = AttributeValue::where('attribute_id', $attr_from->id)->get();

      foreach($values as $key => $value) {
        // $this->info($value->old_value);
        $this->toPromAttrQVW($value);
      }
    }

    /**
     * removeDublicatesRelations
     *
     * @return void
     */
    private function removeDublicatesRelations() {
      $processed_ids = [];
      $all_aps = AttributeProduct::where('to_delete', 0)->get();

      foreach($all_aps as $ap) {
        $id = (int)$ap->id;

        // Checkbox and radio type
        if(!empty($ap->attribute_value_id)) {

          $simular_aps = AttributeProduct::
            where('attribute_value_id', $ap->attribute_value_id)
            ->where('attribute_id', $ap->attribute_id)
            ->where('product_id', $ap->product_id)
            ->get();
          
          foreach($simular_aps as $simular_ap) {
            if(!in_array($simular_ap->id, $processed_ids) && $simular_ap->id !== $id) {
              $simular_ap->to_delete = 1;
              $simular_ap->save();
            }
          }

          $processed_ids[] = $id;

        }
        // Number type
        else if(!empty($ap->value)) {

          $simular_aps = AttributeProduct::
            where('value', $ap->value)
            ->where('attribute_id', $ap->attribute_id)
            ->where('product_id', $ap->product_id)
            ->get();

          
          foreach($simular_aps as $simular_ap) {
            if(!in_array($simular_ap->id, $processed_ids) && $simular_ap->id !== $id) {
              $simular_ap->to_delete = 1;
              $simular_ap->save();
            }
          }

          $processed_ids[] = $id;
        }
      }

    }


    
    /**
     * mergeDublicateCheckboxValues
     *
     * @return void
     */
    private function mergeDublicateCheckboxValues() {
      // $value = 'Розовый';
      // $av = AttributeValue::where(\DB::raw('lower(value)'), 'like',  mb_strtolower($value))->get();
      // ->whereJsonContains('options->languages', mb_strtolower('Желтый'))

      // $av = AttributeValue::where(\DB::raw('lower(value->"$.ru")'), mb_strtolower($value))->get();
      // dd($av);
      $processed_ids = [];
      $avs = AttributeValue::where('to_delete', 0)->get();

      foreach($avs as $av) {
        //
        if($av->to_delete) {
          continue;
        }

        // All other values of this attribute
        $this_attr_values = AttributeValue::
            where('id', '!=', $av->id)
          ->where('attribute_id', $av->attribute_id)
          ->where('to_delete', 0)
          ->get();

        foreach($this_attr_values as $value) {
          // skip if already processed
          if(in_array($value->id, $processed_ids)) {
            continue;
          }

          // compare values and merge if equal
          if(mb_strtolower($av->value) == mb_strtolower($value->value)){
            // Give products relation of this processing value dublicate
            $aps = AttributeProduct::where('attribute_value_id', $value->id)->get();

            // Attach all product to this (base) value
            foreach($aps as $ap) {
              // save old id
              $ap->old_av_id = $ap->attribute_value_id;
              // change value id to this (base)
              $ap->attribute_value_id = $av->id;
              $ap->save();
            }
            
            // Save id as processed
            $processed_ids[] = $value->id;

            // Set old value to delete
            $value->to_delete = 1;
            $value->save();
          }

        }

        // Set this id as processed
        $processed_ids[] = $av->id;

      }
    }
}
