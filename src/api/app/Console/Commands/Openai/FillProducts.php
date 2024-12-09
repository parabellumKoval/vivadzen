<?php

namespace App\Console\Commands\Openai;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Models\Category;
use App\Models\Product;

use OpenAI;

class FillProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openai:fill-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update catalog cache';

    protected $client = null;

    private $available_languages = [];

    private $langs_list = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
      parent::__construct();
      $this->setClient();

      // available languages
      $this->available_languages = config('backpack.crud.locales');
      $this->langs_list = array_keys($this->available_languages);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      $this->fillProducts();
    }
        
    /**
     * setClient
     *
     * @return void
     */
    private function setClient() {
      $yourApiKey = config('openai.key');
      $this->client = OpenAI::client($yourApiKey);
    }

    /**
     * fillProducts
     *
     * @return void
     */
    private function fillProducts() {

      // $this->createThread();

      // $this->openaiCall();
      // $this->getModelsList();

      $langs_list = $this->langs_list;

	    $products = Product::whereHas('sp', function($query){
        $query->where('in_stock', '>', 0);
      })->where(function($query) use($langs_list) {
        foreach($langs_list as $lang_key) {
          $query->whereRaw('LENGTH(JSON_EXTRACT(content, "$.' . $lang_key . '")) < ? ', 150);
        }

        $query->orWhere('content', null);
      })->take(10);
      
      $products_count = $products->count();
      $products_cursor = $products->cursor();

      $bar = $this->output->createProgressBar($products_count);
      $bar->start();

      foreach($products_cursor as $product) {
        $content = $this->getProductContent($product->name);

        if(!$content) {
          continue;
        }

        $product->setTranslation('content', 'uk', $content);
        $product->save();

        $this->info('Product ' . $product->id . ' - https://djini.com.ua/' . $product->slug  . ' processed');
        $bar->advance();
      }

      $bar->finish();
    }
    
    /**
     * messageToThread
     *
     * @return void
     */
    private function messageToThread() {
      $response = $client->threads()->messages()->create('thread_mPmn2V8CMqiw361uiV06OsMz', [
        'role' => 'user',
        'content' => 'Детокс очищение организма DETOX 7+Ionic Form для начала похудения/программа на 25 дней, Garo Nutrition',
     ]);
    }
        
    /**
     * createThread
     *
     * @return void
     */
    private function createThread() {
      $response = $this->client->threads()->create([]);
      dd($response->toArray());
      // thread_mPmn2V8CMqiw361uiV06OsMz
    }
    
    /**
     * openaiCall
     *
     * @return void
     */
    private function getProductContent($name = null) {

      if(!$name) {
        return null;
      }

      $result = $this->client->chat()->create([
        'model' => 'gpt-4o',
        'messages' => [
          [
            'role' => 'system', 
            'content' => config('openai.system')
          ],
          [
            'role' => 'user', 
            'content' => $name
          ],
        ],
      ]);

      // dd($result->choices[0]->message->content, $result);

      return $result->choices[0]->message->content ?? null;
    }

    
    /**
     * createAssistant
     *
     * @return void
     */
    private function createAssistant() {
      $response = $this->client->assistants()->create([
          'instructions' => 'You are a personal math tutor. When asked a question, write and run Python code to answer the question.',
          'name' => 'Math Tutor',
          'tools' => [
              [
                  'type' => 'code_interpreter',
              ],
          ],
          'model' => 'gpt-4',
      ]);
    }
    
    /**
     * getModelsList
     *
     * @return void
     */
    private function getModelsList() {
      try {
        $response = $this->client->models()->list();
        $models = $response->data;
      }catch(\Exception $e) {
        return $e->getMessage();
      }

      $names = collect($models)->pluck('id');
      return $names;
    }
}
