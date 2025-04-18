<?php

namespace App\Console\Commands\Transform;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Brand;

use Backpack\Settings\app\Models\Settings;
use App\Models\TranslationHistory;
use App\Exceptions\TranslationException;

use \DeepL\Translator;

class TranslateBrands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:brands';

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

      if (!isset($this->opts['translate_brands']) || !$this->opts['translate_brands']) {
        $this->info('Brands translation is disabled in settings. Exiting.');
        return;
      }

      $this->translateBrands();

      return 0;
    }
        
    /**
     * Method translateBrand
     *
     * @param $brand $brand [explicite description]
     * @param $content $content [explicite description]
     * @param $from $from [explicite description]
     * @param $to $to [explicite description]
     *
     * @return void
     */
    private function translateBrand($brand, $content, $from, $to) {
      try {
        $result = $this->translator->translateText([
          $content
        ], $from, $to, ['tag_handling' => 'html']);
      }catch(\Exception $e) {
        throw new TranslationException($e->getMessage(), 'deepl');
      }

      try {
        $brand->setTranslation('content', $to, $result[0]->text);
        $brand->is_trans = 1;
        $brand->save();
      }catch(\Exception $e) {
        throw new TranslationException($e->getMessage(), 'set_translation');
      }
    }

    /**
     * Method translateBrands
     *
     * @return void
     */
    private function translateBrands() {

      $only_active = isset($this->opts['active_brands_only']) && $this->opts['active_brands_only'] ? 1 : 0;

      $brands = Brand::whereNotNull('content')
                      ->where('is_trans', 0)
                      ->when($only_active, function($query) {
                        $query->where('is_active', 1);
                      })
                      ->where(function($query) {
                          $conditions = [];
                          $min_symbols = 0;
                          foreach ($this->langs_list as $lang_key) {
                              $conditions[] = '(JSON_EXTRACT(content, "$.' . $lang_key . '") IS NOT NULL AND JSON_UNQUOTE(JSON_EXTRACT(content, "$.' . $lang_key . '")) != "null" AND JSON_UNQUOTE(JSON_EXTRACT(content, "$.' . $lang_key . '")) != "")';
                          }
                          $query->whereRaw('(' . implode(' + ', $conditions) . ') = 1');
                      })->get();

      $bar = $this->output->createProgressBar($brands->count());
      $bar->start();

      foreach($brands as $brand) {

        $ru_content = $brand->getTranslation('content', 'ru', false);
        $uk_content = $brand->getTranslation('content', 'uk', false);
        
        if(empty($ru_content) && empty($uk_content)) {
          $this->error("У бренда id = {$brand->id} нет заполненного контента");
          continue;
        }

        $th = TranslationHistory::createItem($brand);

        try {
          if(!empty($ru_content)) {
            $this->translateBrand($brand, $ru_content, 'ru', 'uk');
          }else if(!empty($uk_content)) {
            $this->translateBrand($brand, $uk_content, 'uk', 'ru');
          }
        } catch(TranslationException $e) {
          $th->updateStatus('error', $e->getMessage());

          $this->line($e->getMessage());

          // if Quota error from deepl skip trying for all other products 
          if($e->getKey() == 'deepl') {
            return;
          }
        }

        $th->updateStatus('done', '');
        $this->info('Brand ' . $brand->id . ' translated');

        $bar->advance();
      }

      $bar->finish();
      
    }

}
