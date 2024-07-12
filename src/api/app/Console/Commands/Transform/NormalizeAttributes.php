<?php

namespace App\Console\Commands\Transform;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product;
use Backpack\Store\app\Models\Attribute;
use Backpack\Store\app\Models\AttributeValue;
use Backpack\Store\app\Models\AttributeProduct;

class NormalizeAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:normalize-attributes';

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
      // $this->attrCountry();

      // Remove bad (small, unused) site attributes
      // $this->attrsToRemove();

      // Join Prom attribute to existed site attribute
      // $this->joinAttrs();

      // Merge simular prom attributes to single attr
      // $this->mergePromAttrs();

      // Move site Volume attribute to multiple prom attributes
      // $this->attrVolume();
      
      // Join (remove) simular AttributeValue and AttributeProduct to single
      // $this->joinDublicatesToSingle();
      
      //
      // $this->restoreDublicateCheckboxValues();
      $this->removeDublicateCheckboxValues();
      
      return 0;
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
     * removeDublicateCheckboxValues
     *
     * @return void
     */
    private function removeDublicateCheckboxValues() {
      // $value = 'Розовый';
      // $av = AttributeValue::where(\DB::raw('lower(value)'), 'like',  mb_strtolower($value))->get();
      // ->whereJsonContains('options->languages', mb_strtolower('Желтый'))

      // $av = AttributeValue::where(\DB::raw('lower(value->"$.ru")'), mb_strtolower($value))->get();
      // dd($av);
      $avs = AttributeValue::where('attribute_id', 14)->get();

      foreach($avs as $av) {
        //
        if($av->to_delete) {
          continue;
        }

        $this_attr_values = AttributeValue::
            where('id', '!=', $av->id)
          ->where('attribute_id', $av->attribute_id)
          ->where('to_delete', 0)
          ->get();
        

        // dd($this_attr_values->count());
        // dd(mb_strtolower($av->value));

        foreach($this_attr_values as $value) {
          if(mb_strtolower($av->value) == mb_strtolower($value->value)){
            $aps = AttributeProduct::where('attribute_value_id', $value->id)->get();

            foreach($aps as $ap) {
              $ap->old_av_id = $ap->attribute_value_id;
              $ap->attribute_value_id = $av->id;
              $ap->save();
            }
            
            $value->to_delete = 1;
            $value->save();
          }
        }
      }
    }

    /**
     * joinDublicatesToSingle
     *
     * @return void
     */
    private function joinDublicatesToSingle() {
      $processed_ids = [];
      $all_aps = AttributeProduct::where('to_delete', 0)->get();

      foreach($all_aps as $ap) {
        $id = (int)$ap->id;

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

        }else if(!empty($ap->value)) {

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
     * mergePromAttrs
     *
     * @return void
     */
    private function mergePromAttrs() {
      
      // Вес, г
      $prom_weight_id = 850;

      // Масса, г
      $prom_mass_id = 1030;

      $this->mergePromSimular($prom_mass_id, $prom_weight_id);

      // Колиечство, шт
      $prom_quant_id = 840;

      // Количество в упаковке, шт
      $prom_quant_pack_id = 833;

      $this->mergePromSimular($prom_quant_pack_id, $prom_quant_id);
    }
    
    private function mergePromSimular($from_id, $to_id) {
      $aps = AttributeProduct::where('attribute_id', $from_id)->get();

      foreach($aps as $ap) {
        $ap->update([
          'attribute_id' => $to_id
        ]);
      }

      $attr = Attribute::find($from_id);
      $attr->to_delete = 1;
      $attr->save();
    }


    /**
     * joinAttrs
     *
     * @return void
     */
    private function joinAttrs() {
      // $this->joinColor();

      // Цвет
      $color_site_id = 14;
      $color_prom_id = 829;
      $this->joinPromToSiteAttrs($color_site_id, $color_prom_id);

      // Материал
      $metterial_site_id = 7;
      $metterial_prom_id = 831;
      $this->joinPromToSiteAttrs($metterial_site_id, $metterial_prom_id);

      // Вкус
      $taste_site_id = 13;
      $taste_prom_id = 836;
      $this->joinPromToSiteAttrs($taste_site_id, $taste_prom_id);

      // Тип кожи
      $taste_site_id = 19;
      $taste_prom_id = 857;
      $this->joinPromToSiteAttrs($taste_site_id, $taste_prom_id);

      // Назначение
      $taste_site_id = 18;
      $taste_prom_id = 852;
      $this->joinPromToSiteAttrs($taste_site_id, $taste_prom_id);

      // Бренд
      $taste_site_id = 10;
      $taste_prom_id = 827;
      $this->joinPromToSiteAttrs($taste_site_id, $taste_prom_id);

      // Форма выпуска
      $taste_site_id = 5;
      $taste_prom_id = 832;
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
        $new_value = AttributeValue::where('attribute_id', $attr_site_id)->where('old_value', $av->value)->first();

        // if no similar value move 
        if(!$new_value) {
          $av->attribute_id = $attr_site_id;
          $av->save();
          $new_value_id = $av->id;
        }else {
          $av->to_delete = 1;
          $av->save();
          $new_value_id = $new_value->id;
        }

        $aps = AttributeProduct::where('attribute_id', $attr_prom_id)->where('attribute_value_id', $av->id)
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
      $aps = AttributeProduct::where('attribute_id', $attr_prom_id)->get();

      foreach($aps as $ap) {
        $av = AttributeValue::where('attribute_id', $attr_site_id)->where('old_value', $ap->value)->first();

        if(!$av) {
          $av = AttributeValue::create([
            'value' => $ap->value,
            'attribute_id' => $attr_site_id
          ]);
        }

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

      $old_attr = Attribute::find($attr_prom_id);

      if($old_attr->type === 'string') 
      {
        $this->joinFromStringAttr($attr_site_id, $attr_prom_id);
      }
      else if($old_attr->type === 'radio' || $old_attr->type === 'checkbox') 
      {
        $this->joinFromCheckboxAttr($attr_site_id, $attr_prom_id);
      }
      
      $old_attr->to_delete = 1;
      $old_attr->save();
    }

    /**
     * joinColor
     *
     * @return void
     */
    private function joinColor() {

      $attr_site_id = 14;
      $attr_prom_id = 829;

      // For each PROM value
      $avs = AttributeValue::where('attribute_id', $attr_prom_id)->get();

      foreach($avs as $av) {
        $new_value = AttributeValue::where('attribute_id', $attr_site_id)->where('value', $av->value)->first();
        
        // if no similar value move 
        if(!$new_value) {
          $av->attribute_id = $attr_site_id;
          $av->save();
          $new_value_id = $av->id;
        }else {
          $av->to_delete = 1;
          $av->save();
          $new_value_id = $new_value->id;
        }

        $aps = AttributeProduct::where('attribute_id', $attr_prom_id)->where('attribute_value_id', $av->id)
                ->update([
                  'attribute_id' => $attr_site_id,
                  'attribute_value_id' => $new_value_id
                ]);

      }

      $old_attr = Attribute::find($attr_prom_id);
      $old_attr->to_delete = 1;
      $old_attr->save();
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
        15, // Цель
        20, // Количество игл
        21, // Длина
        22, // Ширина
        23, // Область применения
      ];

      foreach($attr_ids as $id) {
        $attr = Attribute::find($id);
        $attr->to_delete = 1;
        $attr->save();
  
        AttributeProduct::where('attribute_id', $id)->delete();

        AttributeValue::where('attribute_id', $id)->update([
          'to_delete' => 1
        ]);

      }


    }

    private function attrCountry() {
      // Страна производства
      $attr = Attribute::find(8);
      // Страна производитель      
      $country_id = 3;

      $avs = AttributeValue::where('attribute_id', $attr->id)->get();

      foreach($avs as $av) {
        $new_av = AttributeValue::where('value', 'like', '%' . $av->value . '%')->where('attribute_id', $country_id)->first();
        if(!$new_av) {
          $new_av = AttributeValue::create([
            'attribute_id' => $country_id,
            'value' => null,
            'source' => 'transport'
          ]);
        }

        $aps = AttributeProduct::where('attribute_value_id', $av->id)->get();

        foreach($aps as $ap) {
          $ap->attribute_value_id = $new_av->id;
          $ap->attribute_id = $country_id;
        }

        // Need to delete old checkbox value
        $av->to_delete = 1;
        $av->save();

      }

      // need to delete old attr
      $attr->to_delete = 1;
      $attr->save();
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
        $prom_attr_id = 840;
        $converted_value = $value;
      }else if(in_array($si, ['гр', 'г', 'кг'])) {
        // Вес, г
        $prom_attr_id = 850;

        if($si === 'гр' || $si === 'г') {
          $converted_value = $value;
        }else if($si === 'кг') {
          $converted_value = $value * 1000;
        }
      }else if(in_array($si, ['мл', 'л'])) {
        // Объем, мл
        $prom_attr_id = 828;

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
      $site_volume_id = 12;

      // Объем, мл
      // $prom_volum_id = 828;
      
      // Вес, г
      // $prom_weight_id = 850;


      // Get all SITE volume values
      $values = AttributeValue::where('attribute_id', $site_volume_id)->get();

      foreach($values as $key => $value) {
        // $this->info($value->old_value);
        $this->toPromAttrQVW($value);
      }
    }
}
