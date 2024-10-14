<?php

namespace App\Console\Commands\Transform;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product;
use Backpack\Store\app\Models\Attribute;
use Backpack\Store\app\Models\AttributeValue;
use Backpack\Store\app\Models\AttributeProduct;

use \DeepL\Translator;

class TranslateAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:attributes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    private $authKey = null;
    private $translator = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {

      $this->authKey = config('deepl.key');
      $this->translator = new \DeepL\Translator($this->authKey);
      
      parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      $this->info("Translate Attributes \n");
      $this->translateAttributes();

      $this->info("Translate AttributeValues \n");
      $this->translateAttributeValues();

      $this->info("Translate AttributeProduct \n");
      $this->translateAttributeProduct();

      $this->info("Translate Product CustomProperties \n");
      $this->translateProductCustomProperties();

      return 0;
    }
    
    /**
     * translateAttributes
     *
     * @return void
     */
    private function translateAttributes(){
      $attributes = Attribute::all();
      
      $bar = $this->output->createProgressBar($attributes->count());
      $bar->start();

      foreach($attributes as $attribute) {
        $ru_name = $attribute->getTranslation('name', 'ru', false);
        $uk_name = $attribute->getTranslation('name', 'uk', false);

        // Skip if both translations exists
        if(!empty($ru_name) && !empty($uk_name)) {
          $this->line('skip attribute id ' .$attribute->id . ' - ' . $attribute->name . ' (both translations exists)');
          continue;
        }

        if(!empty($ru_name)) {
          $this->info('translate attribute id ' . $attribute->id . ' - ' . $attribute->name . ' from RU to UK');
          $result = $this->translator->translateText($ru_name, 'ru', 'uk', ['tag_handling' => 'html']);
          $attribute->setTranslation('name', 'uk', $result->text);
        }elseif(!empty($uk_name)) {
          $this->info('translate attribute id ' . $attribute->id . ' - ' . $attribute->name . ' from UK to RU');
          $result = $this->translator->translateText($uk_name, 'uk', 'ru', ['tag_handling' => 'html']);
          $attribute->setTranslation('name', 'ru', $result->text);
        }

        $attribute->save();

        $bar->advance();
      }

      $bar->finish();
    }


    /**
     * translateAttributeValues
     *
     * @return void
     */
    private function translateAttributeValues(){
      $avs = AttributeValue::all();
      
      $bar = $this->output->createProgressBar($avs->count());
      $bar->start();

      foreach($avs as $av) {
        $ru_value = $av->getTranslation('value', 'ru', false);
        $uk_value = $av->getTranslation('value', 'uk', false);

        // Skip if both translations exists
        if(!empty($ru_value) && !empty($uk_value)) {
          $this->line('ru - ' . $ru_value . ' / uk - ' . $uk_value);
          $this->line('skip attribute value id ' . $av->id . ' - ' . $av->value . ' (both translations exists)');
          continue;
        }

        if(!empty($ru_value)) {
          $result = $this->translator->translateText($ru_value, 'ru', 'uk', ['tag_handling' => 'html']);
          $av->setTranslation('value', 'uk', $result->text);
        }elseif(!empty($uk_value)) {
          $result = $this->translator->translateText($uk_value, 'uk', 'ru', ['tag_handling' => 'html']);
          $av->setTranslation('value', 'ru', $result->text);
        }

        $av->save();

        $bar->advance();
      }

      $bar->finish();
    }
  

    /**
     * translateAttributeProduct
     *
     * @return void
     */
    private function translateAttributeProduct(){
      $aps = AttributeProduct::whereNotNull('value_trans')
                            ->where('value_trans', '!=', '')
                            ->get();
      
      $bar = $this->output->createProgressBar($aps->count());
      $bar->start();

      foreach($aps as $ap) {
        $ru_value = $ap->getTranslation('value_trans', 'ru', false);
        $uk_value = $ap->getTranslation('value_trans', 'uk', false);

        // Skip if both translations exists
        if(!empty($ru_value) && !empty($uk_value)) {
          $this->line('ru - ' . $ru_value . ' / uk - ' . $uk_value);
          $this->line('skip attribute product id ' . $ap->id . ' - ' . $ap->value_trans . ' (both translations exists)');
          continue;
        }

        if(!empty($ru_value)) {
          $result = $this->translator->translateText($ru_value, 'ru', 'uk', ['tag_handling' => 'html']);
          $ap->setTranslation('value_trans', 'uk', $result->text);
        }elseif(!empty($uk_value)) {
          $result = $this->translator->translateText($uk_value, 'uk', 'ru', ['tag_handling' => 'html']);
          $ap->setTranslation('value_trans', 'ru', $result->text);
        }

        $ap->save();

        $bar->advance();
      }

      $bar->finish();
    }


    /**
     * translateProductCustomProperties
     *
     * @return void
     */
    private function translateProductCustomProperties(){
      $products = Product::whereNotNull('extras_trans')
                            ->where('extras_trans', '!=', '');
      
      $products_cursor = $products->cursor();
      
      $bar = $this->output->createProgressBar($products->count());
      $bar->start();

      foreach($products_cursor as $product) {

        $ru_value = $product->getTranslation('extras_trans', 'ru', false);
        $uk_value = $product->getTranslation('extras_trans', 'uk', false);

        $ru_custom_props = isset($ru_value['custom_attrs']) && !empty($ru_value['custom_attrs'])? $ru_value['custom_attrs']: null;
        $uk_custom_props = isset($uk_value['custom_attrs']) && !empty($uk_value['custom_attrs'])? $uk_value['custom_attrs']: null;


        if(!empty($ru_custom_props) && !empty($uk_custom_props) || empty($ru_custom_props) && empty($uk_custom_props)) {
          $this->info('skip product id ' . $product->id);
          continue;
        }


        if(!empty($ru_custom_props)) {
          $cp_uk = [];
          foreach($ru_custom_props as $cp) {
            $name_string = isset($cp['name']) && !empty($cp['name'])? (string)$cp['name']: '';
            $value_string = isset($cp['value']) && !empty($cp['value'])? (string)$cp['value']: '';

            try {
              $result = $this->translator->translateText([
                $name_string, $value_string
              ], 'ru', 'uk', ['tag_handling' => 'html']);

              $cp_uk[] = [
                'name' => $result[0]->text,
                'value' => $result[1]->text
              ];
            }catch(\Exception $e) {
              $this->error('cant translate $product id ' . $product->id);
            }
          }

          $product->setTranslation('extras_trans', 'uk', ['custom_attrs' => $cp_uk]);
        }else if(!empty($uk_custom_props)) {
          $cp_ru = [];
          foreach($uk_custom_props as $cp) {
            $name_string = isset($cp['name']) && !empty($cp['name'])? (string)$cp['name']: '';
            $value_string = isset($cp['value']) && !empty($cp['value'])? (string)$cp['value']: '';

            try {
              $result = $this->translator->translateText([
                $name_string, $value_string
              ], 'uk', 'ru', ['tag_handling' => 'html']);

              $cp_ru[] = [
                'name' => $result[0]->text,
                'value' => $result[1]->text
              ];
            }catch(\Exception $e) {
              $this->error('cant translate $product id ' . $product->id);
            }
          }

          $product->setTranslation('extras_trans', 'ru', ['custom_attrs' => $cp_ru]);
        }

        $product->save();

        $bar->advance();
      }

      $bar->finish();
    }

}
