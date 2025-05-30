<?php

namespace App\Console\Commands\Openai;

// use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Models\Category;
use App\Models\Product;
use App\Models\AiGenerationHistory;
use \Backpack\Settings\app\Models\Settings;

use App\Console\Commands\Openai\Traits\AiProductTrait;


class FillProductProperties extends BaseAi
{
    use AiProductTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openai:fill-product-properties {ai?} {model?} {temperature?} {test_limits?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update catalog cache';

    const TEST_LIMITS = null;
    const REQUEST_ATTEMPTS = 2;

    const DEFAULT_AI = 'gpt';
    const DEFAULT_MODEL = 'gpt-4.1';
    const DEFAULT_TEMP = 0.2;
    

    private $ai = null;
    private $model = null;
    private $temperature = null;
    private $test_limits = null;


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      $this->ai = $this->argument('ai') ?? self::DEFAULT_AI;
      $this->model = $this->argument('model') ?? self::DEFAULT_MODEL;
      $this->temperature = $this->argument('temperature') ?? self::DEFAULT_TEMP;

      $this->test_limits = $this->argument('test_limits') ?? self::TEST_LIMITS;

      $this->loadSettings();
      $this->fillProductProperties();
    }    
        
    /**
     * Method loadSettings
     *
     * @return void
     */
    private function loadSettings()
    {
        $settings = Settings::where('key', 'ai_generation_settings')->first();
        $this->settings = $settings ? $settings->extras : [];
    }


    /**
     * Method fillProductProperties
     *
     * @return void
     */
    private function fillProductProperties(){
        // Start with filtered query based on settings
        $products = $this->getFilteredProductsQuery();
        $products = $products->whereHas('brand')
                              ->has('ap', '=', 0)
                              ->where(function($query) {
                                $query->whereDoesntHave('aiGenerationHistory')
                                      ->orWhereHas('aiGenerationHistory', function ($q) {
                                                $q->where('extras->field', 'properties');
                                            }, '<=', self::REQUEST_ATTEMPTS);
                              });

        $products_count = $products->count();
        $products_cursor = $products->cursor();

        $bar = $this->output->createProgressBar($products_count);
        $bar->start();

        foreach($products_cursor as $index => $product) {
            if($this->test_limits && $index >= $this->test_limits) {
                break;
            }

            $gh = AiGenerationHistory::createItem($product, ['field' => 'properties', 'ai' => $this->ai, 'model' => $this->model, 'temperature' => $this->temperature]);
            
            try {
                $attributes = $this->getProductProperties($product, 'ru');

                if (!$attributes) {
                    $this->error('No attributes found for product: ' . $product->id);
                    continue;
                }
                
                // Process and save attributes
                $product->setAllPropertiesAi($attributes, 'ru');
                $product->attributes_ai_generated = 1;
                $product->save();

                $gh->updateStatus('done', '');
                $this->info('Processed product ' . $product->id . ' properties');
            } catch(\Exception $e) {
                $gh->updateStatus('error', $e->getMessage());
                $this->error($e->getMessage());
            }
            
            $bar->advance();
        }

        $bar->finish();
    }

         
    /**
     * Method getProductProperties
     *
     * @param $product $product [explicite description]
     *
     * @return void
     */
    private function getProductProperties(Product $product = null, string $lang = 'ru') {
        if(!$product) {
            return null;
        }

        $prompt = $this->loadPromptFromFile('properties.txt');
     
        $user_data = [
            "product_name" => $product->name,
            "brand" => $product->brand->name,
            'properties' => $product->getAvailablePropertiesArray('ru'),
            'lang' => $lang
        ];

        $user_data_json = json_encode($user_data, JSON_UNESCAPED_UNICODE);

        $response = $this->client->chat()->create([
            'model' => $this->model,
            'temperature' => $this->temperature,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $prompt
                ],
                [
                    'role' => 'user',
                    'content' => $user_data_json
                ],
            ],
        ]);

        $result = $this->extractJsonFromOpenAiResponse($response);
        
        // Validate response structure
        if (!$result || !is_array($result)) {
            throw new \Exception('Invalid response format from OpenAI');
        }

        // Ensure response has required sections
        if (!isset($result['specs']) || !isset($result['attrs']) || !isset($result['custom_attr'])) {
            throw new \Exception('Response missing required sections (specs, attrs, or custom_attr)');
        }

        return $result;
    }
}
