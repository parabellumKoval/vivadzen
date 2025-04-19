<?php

namespace App\Console\Commands\Transform;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product;
use App\Models\TranslationHistory;
use App\Exceptions\TranslationException;

use Backpack\Settings\app\Models\Settings;

use \DeepL\Translator;

class TranslateProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $available_languages = [];
    protected $langs_list = [];
    protected $authKey = null;
    protected $translator = null;
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

      parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

      $this->settings = Settings::where('key', 'deep_l_translations')->first();

      if (!$this->settings) {
          $this->error('DeepL translation settings not found.');
          return 1;
      }else {
        $this->opts = $this->settings->extras;
      }
      
      //Check if auto translate is enabled
      if (!isset($this->opts['auto_translate_enabled']) || !$this->opts['auto_translate_enabled']) {
          $this->info('Auto translation is disabled in settings. Exiting.');
          return;
      }

      if (!isset($this->opts['translate_products']) || !$this->opts['translate_products']) {
        $this->info('Products translation is disabled in settings. Exiting.');
        return;
      }

      $this->translateProducts();

      return 0;
    }

    /**
     * translate
     *
     * @param  mixed $products
     * @return void
     */
    private function translateProducts() {

      $is_active_only = $this->opts['active_products_only'] ?? true;
      $in_stock_only = $this->opts['in_stock_products_only'] ?? true;
      

      $products = Product::
          when($is_active_only, function($query) {
            $query->where('is_active', 1);
          })
          ->where('is_trans', 0)
          ->whereHas('sp', function($query) use ($in_stock_only) {
            if ($in_stock_only) {
                $query->where('in_stock', '>', 0);
            }

            if (isset($this->opts['min_price'])) {
                $query->where('price', '>=', $this->opts['min_price']);
            }       
          })
          ->where(function($query) {
            // Считаем количество языков с контентом длиной >= 150
            $conditions = [];
            $min_symbols = $this->opts['min_symbols'] ?? 150;
            foreach ($this->langs_list as $lang_key) {
                $conditions[] = '(LENGTH(JSON_EXTRACT(content, "$.' . $lang_key . '")) >= ' . $min_symbols . ')';
            }
            // Объединяем условия через + и проверяем, что ровно одно истинно
            $query->whereRaw('(' . implode(' + ', $conditions) . ') = 1');

          });
      
      $products_count = $products->count();
      $products_cursor = $products->cursor();
      
      $bar = $this->output->createProgressBar($products_count);
      $bar->start();

      foreach($products_cursor as $product) {
        $this->info('Try for product ' . $product->id);

        try {
          $th = TranslationHistory::createItem($product);
          $this->translateItem($product);
        }catch(TranslationException $e) {
          $th->updateStatus('error', $e->getMessage());

          $this->line($e->getMessage());

          // if Quota error from deepl skip trying for all other products 
          if($e->getKey() == 'deepl') {
            return;
          }
        }

        $th->updateStatus('done', '');

        $bar->advance();
      }

      $bar->finish();
      
    }
    

    /**
     * Method deepLTranslateAndSet
     *
     * @param &$product $product [explicite description]
     * @param $name $name [explicite description]
     * @param $content $content [explicite description]
     * @param $from $from [explicite description]
     * @param $to $to [explicite description]
     *
     * @return void
     */
    private function deepLTranslateAndSet(&$product, $name, $content, $from, $to) {

      try {
        $result = $this->translator->translateText([
          $name,
          $content
        ], $from, $to, ['tag_handling' => 'html']);
      }catch(\Exception $e) {
        throw new TranslationException($e->getMessage(), 'deepl');
      }

      try {
        $product->setTranslation('name', $to, $result[0]->text)
                ->setTranslation('content', $to, $result[1]->text);
      }catch(\Exception $e) {
        throw new TranslationException($e->getMessage(), 'set_translation');
      }
    }

    
    /**
     * Method translateItem
     *
     * @param $product $product [explicite description]
     *
     * @return void
     */
    private function translateItem($product) {

      $ru_name = $product->getTranslation('name', 'ru', false);
      $uk_name = $product->getTranslation('name', 'uk', false);

      $ru_content = $product->getTranslation('content', 'ru', false);
      $uk_content = $product->getTranslation('content', 'uk', false);

      if(empty($ru_content) && empty($uk_content)) {
        throw new TranslationException("У товара id = {$product->id} нет заполненного контента", 'no_content');
      }

      if(!empty($ru_content)) {
        $this->deepLTranslateAndSet($product, $ru_name, $ru_content, 'ru', 'uk');
      }else if(!empty($uk_content)) {
        $this->deepLTranslateAndSet($product, $uk_name, $uk_content, 'uk', 'ru');
      }
      
      $product->is_trans = 1;
      $product->save();

      $this->info('Translate product id ' . $product->id);
    }

}
