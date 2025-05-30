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


class FillProductCategories extends BaseAi
{
    use AiProductTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openai:fill-product-categories {ai?} {model?} {temperature?} {chunk_limit?} {chunk_size?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update catalog cache';

    const CHUNK_SIZE = 100;
    const TEST_CHUNK_LIMITS = null;
    const REQUEST_ATTEMPTS = 3;

    const DEFAULT_AI = 'gpt';
    const DEFAULT_MODEL = 'gpt-4o';
    const DEFAULT_TEMP = 0.2;
    

    private $ai = null;
    private $model = null;
    private $temperature = null;
    private $chunk_size = null;
    private $chunk_limit = null;


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

      $this->chunk_size = $this->argument('chunk_size') ?? self::CHUNK_SIZE;
      $this->chunk_limit = $this->argument('chunk_limit') ?? self::TEST_CHUNK_LIMITS;

      $this->loadSettings();
      $this->fillProductCategories();
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
     * Method fillProductCategory
     *
     * @return void
     */
    private function fillProductCategories() {
        // Start with filtered query based on settings
        $products = $this->getFilteredProductsQuery();
        $products->with('brand')
            ->has('categories', '=', 0)
            ->where(function($query) {
              $query->whereDoesntHave('aiGenerationHistory')
                    ->orWhereHas('aiGenerationHistory', function ($q) {
                              $q->where('extras->field', 'category');
                          }, '<=', self::REQUEST_ATTEMPTS);
            })
            ->select('id', 'name', 'brand_id');

        $chunks = $products->cursor()->chunk($this->chunk_size);
        $chunks_count = $chunks->count();

        $bar = $this->output->createProgressBar($chunks_count);
        $bar->start();

        foreach ($chunks as $index => $chunk) {
            if($this->chunk_limit && $index >= $this->chunk_limit) {
                break;
            }

            $productData = $chunk->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand ? $product->brand->name : null
                ];
            })->toArray();
            
            $response = $this->getProductCategories($productData);

            if(!$response) {
                $this->error('No response from OpenAI');
                continue;
            }

            // Process response and update product categories
            $this->updateProductCategories($response);

            $bar->advance();
        }

        $bar->finish();
    }


    /**
     * Method getProductCategories
     *
     * @param $productData $productData [explicite description]
     *
     * @return void
     */
    private function getProductCategories($productData) {
        $prompt = $this->loadPromptFromFile('categories.txt');
        $user_data = [
            "products" => $productData,
            "categories" => Category::getAvailableCategoriesArray('ru')
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

        return $this->extractJsonFromOpenAiResponse($response);
    }

    /**
     * Method updateProductCategories
     *
     * @param array $data Product category data
     * @return void
     */
    private function updateProductCategories($data) {
        foreach ($data as $item) {
            $product = Product::find((int)$item['p_id']);
            
            if(!$product) {
                $this->error('Product not found: ' . $item['p_id']);
                continue;
            }

            $gh = AiGenerationHistory::createItem($product, ['field' => 'category', 'ai' => $this->ai, 'model' => $this->model, 'temperature' => $this->temperature]);
            
            if(!isset($item['c_id']) || $item['c_id'] === null) {
                $this->error('Category not present in response');
                $gh->updateStatus('error', 'Category not present in response');
                continue;
            }

            try {
                $category = Category::find($item['c_id']);
                
                if (!$category) {
                    $this->error('No valid category found for product: ' . $item['p_id']);
                    $gh->updateStatus('error', 'No valid category found');
                    continue;
                }

                $product->categories()->sync([$category->id]);
                $product->category_ai_generated = 1;
                $product->save();

                $this->info("Product " . $product->id . " category updated to: " . $category->name);
                $gh->updateStatus('done', '');
            } catch(\Exception $e) {
                $gh->updateStatus('error', $e->getMessage());
                $this->error($e->getMessage());
            }
        }
    }
}
