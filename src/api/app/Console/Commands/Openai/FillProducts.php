<?php

namespace App\Console\Commands\Openai;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Prompt;
use App\Models\AiGenerationHistory;
use App\Exceptions\HistoryException;

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
    protected $settings = null;

    private $available_languages = [];

    private $langs_list = [];

    const BRAND_CHUNK_SIZE = 100;
    const CATEGORY_CHUNK_SIZE = 100;

    const TEST_CHUNK_LIMITS = 2;
    const TEST_LIMITS = 100;
    

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
      $this->loadSettings();

      if (!isset($this->settings['auto_generation_enabled']) || !$this->settings['auto_generation_enabled']) {
          $this->info('AI generation is disabled in settings');
          return;
      }

      if ($this->settings['generate_description'] ?? false) {
          // Fill content
          $prompts = Prompt::where('is_active', 1)->get();
          foreach($prompts as $prompt) {
              if($prompt->categories) {
                  foreach($prompt->categories as $category) {
                      $this->fillProductsContent($prompt->content, $category->nodeIds);
                  }
              }
          }
      }

      if ($this->settings['fill_characteristics'] ?? false) {
          $this->fillProductProperties();
      }

      if ($this->settings['detect_category'] ?? false) {
          $this->fillProductCategory();
      }

      if ($this->settings['detect_brand'] ?? false) {
          $this->fillProductBrands();
      }
    }
    
    /**
     * Method getFilteredProductsQuery
     *
     * @return void
     */
    private function getFilteredProductsQuery()
    {
        $query = Product::query();

        if ($this->settings['active_products_only'] ?? false) {
            $query->where('is_active', 1);
        }

        if ($this->settings['in_stock_products_only'] ?? false) {
            $query->whereHas('sp', function($q) {
                $q->where('in_stock', '>', 0);
            });
        }

        if (isset($this->settings['min_price']) && $this->settings['min_price'] > 0) {
            $query->whereHas('sp', function($q) {
                $q->where('price', '>=', $this->settings['min_price']);
            });
        }

        return $query;
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
     * Method loadSettings
     *
     * @return void
     */
    private function loadSettings()
    {
        $settings = \Backpack\Settings\app\Models\Settings::where('key', 'ai_generation_settings')->first();
        $this->settings = $settings ? $settings->extras : [];
    }

    
    /**
     * Method updateProductBrands
     *
     * @param $data $data [explicite description]
     *
     * @return void
     */
    private function updateProductBrands($data){
      
      foreach ($data as $item) {

        $product = Product::find((int)$item['p_id']);
        
        if(!$product) {
          $this->error('Product not found: ' . $item['p_id']);
          continue;
        }

        $gh = AiGenerationHistory::createItem($product, ['field' => 'brand']);
        
        if(isset($item['b_name'])){
          $b_name = $item['b_name'];

          $brand = Brand::where(function($query) use($b_name) {
            foreach($this->langs_list as $index => $lang_key) {
              $function_name = $index === 0? 'whereRaw': 'orWhereRaw';
              $query->{$function_name}('LOWER(JSON_EXTRACT(name, "$.' . $lang_key . '")) LIKE ? ',['"' . mb_trim(mb_strtolower($b_name)) . '"']);
            }
          })->first();

          if(!$brand) {
            $brand = new Brand();
          }

          $brand->name = $item['b_name'];
          $brand->is_active = 1;
          $brand->save();

          $this->info('Brand ' . $brand->name . ' created');
        }else if(isset($item['b_id'])) {
          $brand = Brand::find((int)$item['b_id']);

          if(!$brand) {
            $this->error('Brand not found: ' . $item['b_id']);
            $gh->updateStatus('error', 'Brand not found');
            continue;
          }
        }else {
          $this->error('Brand not present in response');
          $gh->updateStatus('error', 'Brand not present in response');
          continue;
        }
        
        try {
          $product->brand_id = $brand->id;
          $product->brand_ai_generated = 1;
          $product->save();
        } catch(\Exception $e) {
            $gh->updateStatus('error', $e->getMessage());
            $this->error($e->getMessage());
        }
        
        $this->info("For product " . $product->id . " brand set to " . $brand->name);
        $gh->updateStatus('done', '');
      }
    }

    
    /**
     * Method fillProductBrands
     *
     * @return void
     */
    private function fillProductBrands() {
        // Start with filtered query based on settings
        $products = $this->getFilteredProductsQuery();
        $products->with('brand')
            ->has('brand', '=', 0)
            ->select('id', 'name');

        $chunks = $products->cursor()->chunk(self::BRAND_CHUNK_SIZE);
        $chunks_count = $chunks->count();

        $bar = $this->output->createProgressBar($chunks_count);
        $bar->start();

        foreach ($chunks as $index => $chunk) {
          if(self::TEST_CHUNK_LIMITS && $index >= self::TEST_CHUNK_LIMITS) {
            break;
          }

            $productData = $chunk->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name
                ];
            })->toArray();
            
            $response = $this->getProductBrands($productData);

            if(!$response) {
              $this->error('No response from OpenAI');
              continue;
            }

            // Process response and update product brand
            $this->updateProductBrands($response);
        }

        $bar->finish();
    }


    /**
     * Method fillProductCategory
     *
     * @return void
     */
    private function fillProductCategory() {
        // Start with filtered query based on settings
        $products = $this->getFilteredProductsQuery();
        $products->with('brand')
            ->has('categories', '=', 0)
            ->select('id', 'name', 'brand_id');

        $chunks = $products->cursor()->chunk(self::CATEGORY_CHUNK_SIZE);
        $chunks_count = $chunks->count();

        $bar = $this->output->createProgressBar($chunks_count);
        $bar->start();

        foreach ($chunks as $index => $chunk) {
            if(self::TEST_CHUNK_LIMITS && $index >= self::TEST_CHUNK_LIMITS) {
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

            $gh = AiGenerationHistory::createItem($product, ['field' => 'category']);
            
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
    
    /**
     * Method getProductBrands
     *
     * @param $productData $productData [explicite description]
     *
     * @return void
     */
    private function getProductBrands($productData) {
        $prompt = $this->loadPromptFromFile('brands.txt');
        $user_data = [
            "products" => $productData,
            "brands" => Brand::getAvailableBrandsArray()
        ];

        $user_data_json = json_encode($user_data, JSON_UNESCAPED_UNICODE);

        $response = $this->client->chat()->create([
            'model' => 'gpt-4o',
            'temperature' => 0.2,
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
            'model' => 'gpt-4o',
            'temperature' => 0.2,
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
     * Method fillProductProperties
     *
     * @return void
     */
    private function fillProductProperties(){
        // Start with filtered query based on settings
        $products = $this->getFilteredProductsQuery();
        $products = $products->whereHas('brand')
                              ->has('ap', '=', 0);

        $products_count = $products->count();
        $products_cursor = $products->cursor();

        $bar = $this->output->createProgressBar($products_count);
        $bar->start();

        foreach($products_cursor as $index => $product) {
            if(self::TEST_LIMITS && $index >= self::TEST_LIMITS) {
                break;
            }

            $gh = AiGenerationHistory::createItem($product, ['field' => 'properties']);
            
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
     * fillProductsContent
     *
     * @return void
     */
    private function fillProductsContent($prompt, $categoryIds) {
        // $prompt = $this->loadPromptFromFile('content.txt');
        $langs_list = $this->langs_list;

        // Start with filtered query based on settings
        $products = $this->getFilteredProductsQuery();
        
        // Add content-specific filters
        $products->where(function($query) use($langs_list) {
            foreach($langs_list as $lang_key) {
                $query->whereRaw('LENGTH(JSON_EXTRACT(content, "$.' . $lang_key . '")) < ? ', 150);
            }
            $query->orWhere('content', null);
        })->whereHas('categories', function($query) use($categoryIds) {
            $query->whereIn('category_id', $categoryIds);
        });

        $products_count = $products->count();
        $products_cursor = $products->cursor();

        $bar = $this->output->createProgressBar($products_count);
        $bar->start();

        foreach($products_cursor as $index => $product) {

            if(self::TEST_LIMITS && $index >= self::TEST_LIMITS) {
              break;
            }

            $gh = AiGenerationHistory::createItem($product, ['field' => 'content']);
            
            try {
                $content = $this->getProductContent($product->name, $prompt);

                if(!$content) {
                    continue;
                }

                $product->setTranslation('content', 'uk', $content);
                $product->is_ai_content = 1;
                $product->save();

                $gh->updateStatus('done', '');
                $this->info('Product ' . $product->id . ' - https://djini.com.ua/' . $product->slug  . ' processed');
            } catch(\Exception $e) {
                $gh->updateStatus('error', $e->getMessage());
                $this->error($e->getMessage());
                continue;
            }

            $bar->advance();
        }

        $bar->finish();
    }

    /**
     * openaiCall
     *
     * @return void
     */
    private function getProductContent($name = null, $prompt = null) {

      if(!$name || !$prompt) {
        return null;
      }

      $result = $this->client->chat()->create([
        'temperature' => 0.5,
        'model' => 'gpt-4.1',
        'messages' => [
          [
            'role' => 'system',
            'content' => $prompt
          ],
          [
            'role' => 'user',
            'content' => $name
          ],
        ],
      ]);

      return $result->choices[0]->message->content ?? null;
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
            'model' => 'gpt-4.1',
            'temperature' => 0.2,
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
    
    /**
     * Method extractJsonFromOpenAiResponse
     *
     * @param $response $response [explicite description]
     *
     * @return array
     */
    public function extractJsonFromOpenAiResponse($response): ?array
    {
        if (!isset($response->choices[0]->message->content)) {
            return null;
        }

        $content = trim($response->choices[0]->message->content);

        // Попробуем сразу декодировать
        $data = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $data;
        }

        // Если не получилось — попытаемся вытащить JSON из текста
        if (preg_match('/\[\s*\{.*\}\s*\]/s', $content, $matches)) {
            $json = $matches[0];
            $data = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return $data;
            }
        }

        // Альтернативно — если массив без скобок (реже, но вдруг)
        if (preg_match('/\{\s*"id"\s*:\s*\d+.*?\}/s', $content, $matches)) {
            $json = '[' . $matches[0] . ']';
            $data = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return $data;
            }
        }

        // Вернуть null, если не удалось декодировать
        return null;
    }
    
    /**
     * Method loadPromptFromFile
     *
     * @param string $filename
     * @return string
     */
    private function loadPromptFromFile($filename)
    {
        $path = __DIR__ . '/prompts/' . $filename;
        if (!file_exists($path)) {
            throw new \RuntimeException("Prompt file not found: {$filename}");
        }
        return file_get_contents($path);
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

    /**
     * messageToThread
     *
     * @return void
     */
    private function messageToThread() {
      $response = $this->client->threads()->messages()->create('thread_mPmn2V8CMqiw361uiV06OsMz', [
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
}
