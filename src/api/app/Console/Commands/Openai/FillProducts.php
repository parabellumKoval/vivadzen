<?php

namespace App\Console\Commands\Openai;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Models\Category;

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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
      parent::__construct();
      $this->setClient();
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

      // $this->openaiCall();
      $this->getModelsList();

	    // $products = Product::where('is_active', 1);
      // $products_count = $products->count();
      // $products_cursor = $products->cursor();

      // $bar = $this->output->createProgressBar($products_count);
      // $bar->start();

      // foreach($products_cursor as $product) {
      //   $bar->advance();
      // }

      // $bar->finish();
    }

    
    /**
     * openaiCall
     *
     * @return void
     */
    private function openaiCall() {

      $result = $this->client->chat()->create([
          'model' => 'gpt-4',
          'messages' => [
              ['role' => 'user', 'content' => 'Привет, поможешь создать описание товара?'],
          ],
      ]);

      dd($result->choices[0]->message->content, $result);
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
