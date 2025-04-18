<?php

namespace App\Console\Commands\Transform;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product;
use Backpack\Store\app\Models\Attribute;
use Backpack\Store\app\Models\AttributeValue;
use Backpack\Store\app\Models\AttributeProduct;

use Backpack\Settings\app\Models\Settings;
use App\Models\TranslationHistory;
use App\Exceptions\HistoryException;

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


    protected $available_languages = [];
    protected $langs_list = [];
    private $authKey = null;
    private $translator = null;
    protected $settings = null;
    protected $opts = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
      $this->available_languages = config('backpack.crud.locales');
      $this->langs_list = array_keys($this->available_languages);

      $this->authKey = config('deepl.key');
      $this->translator = new \DeepL\Translator($this->authKey);
      
      $this->settings = Settings::where('key', 'deep_l_translations')->first();

      if (!$this->settings) {
          $this->error('DeepL translation settings not found.');
          return 1;
      }else {
        $this->opts = $this->settings->extras;
      }

      parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

      //Check if auto translate is enabled
      if (!isset($this->opts['auto_translate_enabled']) || !$this->opts['auto_translate_enabled']) {
        $this->info('Auto translation is disabled in settings. Exiting.');
        return;
      }

      if (!isset($this->opts['translate_specs']) || !$this->opts['translate_specs']) {
        $this->info('Attributes translation is disabled in settings. Exiting.');
        return;
      }

      // Check
      if (isset($this->opts['translate_attribute_names']) && $this->opts['translate_attribute_names']) {
        $this->info("Translate Attributes \n");
        $this->translateAttributes();
      }

      if(isset($this->opts['translate_attribute_values']) && $this->opts['translate_attribute_values']) {
        $this->info("Translate AttributeValues \n");
        $this->translateAttributeValues();
      }

      if(isset($this->opts['translate_product_attribute_values']) && $this->opts['translate_product_attribute_values']) {
        $this->info("Translate AttributeProduct \n");
        $this->translateAttributeProducts();
      }

      if(isset($this->opts['unique_product_specs']) && $this->opts['unique_product_specs']) {
        $this->info("Translate Product CustomProperties \n");
        $this->translateProductCustomProperties();
      }

      return 0;
    }
        
    /**
     * Method translateAttibute
     *
     * @param &$attribute $attribute [explicite description]
     * @param $from $from [explicite description]
     * @param $to $to [explicite description]
     * @param $name $name [explicite description]
     *
     * @return void
     */
    private function translateAttibute(&$attribute, $from, $to, $name) {
      $this->info("translate attribute id {$attribute->id} / {$attribute->name} from {$from} to {$to}");

      try {
        $result = $this->translator->translateText($name, $from, $to, ['tag_handling' => 'html']);
      }catch(\Exception $e) {
        throw new HistoryException($e->getMessage(), 'deepl');
      }
      
      try {
        $attribute->setTranslation('name', $to, $result->text);
        $attribute->save();
      }catch(\Exception $e) {
        throw new HistoryException($e->getMessage(), 'set_translation');
      }

    }
    /**
     * translateAttributes
     *
     * @return void
     */
    private function translateAttributes(){
      $attributes = Attribute::where(function($query) {
        // Считаем количество языков с контентом длиной >= 150
        $conditions = [];
        $min_symbols = 0;
        foreach ($this->langs_list as $lang_key) {
            $conditions[] = '(JSON_EXTRACT(name, "$.' . $lang_key . '") IS NOT NULL AND JSON_UNQUOTE(JSON_EXTRACT(name, "$.' . $lang_key . '")) != "")';
        }
        // Объединяем условия через + и проверяем, что ровно одно истинно
        $query->whereRaw('(' . implode(' + ', $conditions) . ') = 1');

      })->get();

      $bar = $this->output->createProgressBar($attributes->count());
      $bar->start();

      foreach($attributes as $attribute) {
        $ru_name = $attribute->getTranslation('name', 'ru', false);
        $uk_name = $attribute->getTranslation('name', 'uk', false);

        // Skip if both translations exists
        if(!empty($ru_name) && !empty($uk_name) || empty($ru_name) && empty($uk_name)) {
          $this->line('skip attribute id ' .$attribute->id . ' - ' . $attribute->name . ' (both names exists or not exists)');
          continue;
        }

        $th = TranslationHistory::createItem($attribute);

        try {
          if(!empty($ru_name)) {
            $this->translateAttibute($attribute, 'ru', 'uk', $ru_name);
          }elseif(!empty($uk_name)) {
            $this->translateAttibute($attribute, 'uk', 'ru', $uk_name);
          }
        }catch(\HistoryException $e) {
          $th->updateStatus('error', $e->getMessage());
          $this->error($e->getMessage());

          if($e->getKey() == 'deepl') {
            return;
          }
        }

        $th->updateStatus('done', '');
        $this->info('translated attribute id ' . $attribute->id . ' - ' . $attribute->name);

        $bar->advance();
      }

      $bar->finish();
    }

    
    /**
     * Method translateAttibuteValue
     *
     * @param $av $av [explicite description]
     * @param $from $from [explicite description]
     * @param $to $to [explicite description]
     * @param $value $value [explicite description]
     *
     * @return void
     */
    private function translateAttibuteValue($av, $from, $to, $value) {
      $this->info("translate attribute value id {$av->id} / {$av->value} from {$from} to {$to}");
      
      try {
        $result = $this->translator->translateText($value, $from, $to, ['tag_handling' => 'html']);
      }catch(\Exception $e) {
        throw new HistoryException($e->getMessage(), 'deepl');
      }
      
      try {
        $av->setTranslation('value', $to, $result->text);
        $av->save();
      }catch(\Exception $e) {
        throw new HistoryException($e->getMessage(), 'set_translation');
      }

    }

    /**
     * translateAttributeValues
     *
     * @return void
     */
    private function translateAttributeValues(){
      $avs = AttributeValue::where(function($query) {
        // Считаем количество языков с контентом длиной >= 150
        $conditions = [];
        $min_symbols = 0;
        foreach ($this->langs_list as $lang_key) {
            $conditions[] = '(JSON_EXTRACT(value, "$.' . $lang_key . '") IS NOT NULL AND JSON_UNQUOTE(JSON_EXTRACT(value, "$.' . $lang_key . '")) != "")';
        }
        // Объединяем условия через + и проверяем, что ровно одно истинно
        $query->whereRaw('(' . implode(' + ', $conditions) . ') = 1');

      })->get();

      
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

        $th = TranslationHistory::createItem($av);

        try {
          if(!empty($ru_value)) {
            $this->translateAttibuteValue($av, 'ru', 'uk', $ru_value);
          }elseif(!empty($uk_value)) {
            $this->translateAttibuteValue($av, 'uk', 'ru', $uk_value);
          }
        }catch(\HistoryException $e) {
          $th->updateStatus('error', $e->getMessage());
          $this->error($e->getMessage());

          if($e->getKey() == 'deepl') {
            return;
          }
        }

        $th->updateStatus('done', '');
        $this->info('translated attribute value id ' . $av->id . ' - ' . $av->value);

        $bar->advance();
      }

      $bar->finish();
    }
  
    
    /**
     * Method translateAttibuteProduct
     *
     * @param $ap $ap [explicite description]
     * @param $from $from [explicite description]
     * @param $to $to [explicite description]
     * @param $value $value [explicite description]
     *
     * @return void
     */
    private function translateAttibuteProduct($ap, $from, $to, $value) {
      $this->info("translate attribute product id {$ap->id} / {$ap->value} from {$from} to {$to}");

      try {
        $result = $this->translator->translateText($value, $from, $to, ['tag_handling' => 'html']);
      }catch(\Exception $e) {
        throw new HistoryException($e->getMessage(), 'deepl');
      }

      try {
        $ap->setTranslation('value_trans', $to, $result->text);
        $ap->save();
      }catch(\Exception $e) {
        throw new HistoryException($e->getMessage(), 'set_translation');
      }
    }

    /**
     * translateAttributeProducts
     *
     * @return void
     */
    private function translateAttributeProducts(){
      $aps = AttributeProduct::whereNotNull('value_trans')
          ->where('value_trans', '!=', '')
          ->where(function($query) {
            $conditions = [];
            foreach ($this->langs_list as $lang_key) {
                $conditions[] = '(JSON_EXTRACT(value_trans, "$.' . $lang_key . '") IS NOT NULL AND JSON_UNQUOTE(JSON_EXTRACT(value_trans, "$.' . $lang_key . '")) != "")';
            }
            $query->whereRaw('(' . implode(' + ', $conditions) . ') = 1');
          })
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

        $th = TranslationHistory::createItem($ap);

        try {
          if(!empty($ru_value)) {
            $this->translateAttibuteProduct($ap, 'ru', 'uk', $ru_value);
          }elseif(!empty($uk_value)) {
            $this->translateAttibuteProduct($ap, 'uk', 'ru', $uk_value);
          }
        }catch(\HistoryException $e) {
          $th->updateStatus('error', $e->getMessage());
          $this->error($e->getMessage());

          if($e->getKey() == 'deepl') {
            return;
          }
        }
        $th->updateStatus('done', '');
        $this->info('translated attribute product id ' . $ap->id . ' - ' . $ap->value_trans);

        $bar->advance();
      }

      $bar->finish();
    }

    
    /**
     * Method translateAndSaveCustomProperties
     *
     * @param $product $product [explicite description]
     * @param $custom_props $custom_props [explicite description]
     * @param $from $from [explicite description]
     * @param $to $to [explicite description]
     *
     * @return void
     */
    private function translateAndSaveCustomProperties($product, $custom_props, $from, $to) {
        $translated_props = [];
    
        foreach ($custom_props as $cp) {
            $name_string = isset($cp['name']) && !empty($cp['name']) ? (string)$cp['name'] : '';
            $value_string = isset($cp['value']) && !empty($cp['value']) ? (string)$cp['value'] : '';
    
            try {
                // Переводим имя и значение
                $result = $this->translator->translateText(
                    [$name_string, $value_string],
                    $from,
                    $to,
                    ['tag_handling' => 'html']
                );
    
                $translated_props[] = [
                    'name' => $result[0]->text,
                    'value' => $result[1]->text
                ];
            } catch (\Exception $e) {
              throw new HistoryException($e->getMessage(), 'deepl');
            }
        }
    
        try {
            // Присваиваем переведённые данные и сохраняем продукт
            $product->setTranslation('extras_trans', $to, ['custom_attrs' => json_encode($translated_props)]);
            $product->save();
        } catch (\Exception $e) {
          throw new HistoryException($e->getMessage(), 'set_translation');
        }
    
        return true; // Успешно переведено и сохранено
    }
    
    /**
     * Method getCustomPropsFromProduct
     *
     * @param $product $product [explicite description]
     * @param $lang $lang [explicite description]
     *
     * @return void
     */
    private function getCustomPropsFromProduct($product, $lang) {
        // Получаем перевод для указанного языка
        $translation = $product->getTranslation('extras_trans', $lang, false);
    
        // Проверяем, существует ли ключ custom_attrs и не пустое ли значение
        if (isset($translation['custom_attrs']) && !empty($translation['custom_attrs'])) {
            $customProps = $translation['custom_attrs'];
    
            // Если значение строка, пытаемся декодировать JSON
            if (is_string($customProps)) {
                $decoded = json_decode($customProps, true);
    
                // Если декодирование успешно, используем декодированный массив
                if (json_last_error() === JSON_ERROR_NONE) {
                    $customProps = $decoded;
                }
            }
    
            // Возвращаем массив или оригинальное значение
            return $customProps;
        }
    
        // Если ключ отсутствует или значение пустое, возвращаем null
        return null;
    }

    /**
     * translateProductCustomProperties
     *
     * @return void
     */
    private function translateProductCustomProperties(){
      $products = Product::whereNotNull('extras_trans')
                            ->where('extras_trans', '!=', '')
                            ->where(function($query) {
                              $conditions = [];
                              foreach ($this->langs_list as $lang_key) {
                                  $conditions[] = '(JSON_EXTRACT(extras_trans, "$.' . $lang_key . '.custom_attrs") IS NOT NULL AND JSON_UNQUOTE(JSON_EXTRACT(extras_trans, "$.' . $lang_key . '.custom_attrs")) != "null" AND JSON_UNQUOTE(JSON_EXTRACT(extras_trans, "$.' . $lang_key . '.custom_attrs")) != "")';
                              }
                              $query->whereRaw('(' . implode(' + ', $conditions) . ') = 1');
                            });
      
      $products_cursor = $products->cursor();
      
      $bar = $this->output->createProgressBar($products->count());
      $bar->start();

      foreach($products_cursor as $product) {
        $ru_custom_props = $this->getCustomPropsFromProduct($product, 'ru');
        $uk_custom_props = $this->getCustomPropsFromProduct($product, 'uk');

        // Skip if both translations exists
        if(!empty($ru_custom_props) && !empty($uk_custom_props) || empty($ru_custom_props) && empty($uk_custom_props)) {
          $this->info('skip product id ' . $product->id);
          continue;
        }

        $th = TranslationHistory::createItem($product, ['field' => 'custom_props']);

        try {
          if (!empty($ru_custom_props)) {
            $this->translateAndSaveCustomProperties($product, $ru_custom_props, 'ru', 'uk');
          } elseif (!empty($uk_custom_props)) {
              $this->translateAndSaveCustomProperties($product, $uk_custom_props, 'uk', 'ru');
          }
        } catch (\HistoryException $e) {
          $th->updateStatus('error', $e->getMessage());
          $this->error($e->getMessage());

          if ($e->getKey() == 'deepl') {
            return;
          }
        }

        $th->updateStatus('done', '');
        $this->info('translated custom props for product id ' . $product->id);

        $bar->advance();
      }

      $bar->finish();
    }

}
