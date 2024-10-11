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
      // $this->translateAttributes();


      $this->info("Translate AttributeValues \n");
      // $this->translateAttributeValues();


      $this->info("Translate AttributeProduct \n");
      // $this->translateAttributeProduct();

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
        $ru_name = $attribute->getTranslation('name', 'ru');

        $result = $this->translator->translateText($ru_name, 'ru', 'uk', ['tag_handling' => 'html']);

        $attribute->setTranslation('name', 'uk', $result->text);
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
        $ru_value = $av->getTranslation('value', 'ru');

        $result = $this->translator->translateText($ru_value, 'ru', 'uk', ['tag_handling' => 'html']);

        $av->setTranslation('value', 'uk', $result->text);
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

        $ru_value = $ap->getTranslation('value_trans', 'ru');

        $result = $this->translator->translateText($ru_value, 'ru', 'uk', ['tag_handling' => 'html']);

        $ap->setTranslation('value_trans', 'uk', $result->text);
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

        if(!$product->customProperties) {
          continue;
        }

        $cp_uk = [];
        foreach($product->customProperties as $cp) {
          $name_string = isset($cp['name']) && !empty($cp['name'])? (string)$cp['name']: '';
          $value_string = isset($cp['value']) && !empty($cp['value'])? (string)$cp['value']: '';

          // if(gettype($name_string) !== 'string' || gettype($value_string) !== 'string') {
          //   dd('no String', $name_string, $value_string);
          // }
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

        $product->setTranslation('extras_trans', 'uk', [
          'custom_attrs' => $cp_uk
        ]);

        $product->save();

        $bar->advance();
      }

      $bar->finish();
    }

}
