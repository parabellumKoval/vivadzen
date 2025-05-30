<?php

namespace App\Console\Commands\Openai;

// use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Models\Brand;
use App\Models\Product;
use App\Models\AiGenerationHistory;
use \Backpack\Settings\app\Models\Settings;

use App\Console\Commands\Openai\Traits\AiProductTrait;

// use Gemini;
// use Gemini\Data\GenerationConfig;

// use GrokPHP\Client\Clients\GrokClient;
// use GrokPHP\Client\Config\GrokConfig;
// use GrokPHP\Client\Config\ChatOptions;
// use GrokPHP\Client\Enums\Model;
// use GrokPHP\Client\Enums\DefaultConfig;

// use OpenAI;

class FillProductBrands extends BaseAi
{
    use AiProductTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openai:fill-product-brands {ai?} {model?} {temperature?} {chunk_limit?} {chunk_size?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update catalog cache';

    const CHUNK_SIZE = 50;
    const TEST_CHUNK_LIMITS = null;
    const REQUEST_ATTEMPTS = 1;

    const DEFAULT_AI = 'gpt';
    const DEFAULT_MODEL = 'gpt-4.1';
    const DEFAULT_TEMP = 0.4;
    

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
      $this->fillProductBrands();
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
     * Method fillProductBrands
     *
     * @return void
     */
    private function fillProductBrands() {
        // Start with filtered query based on settings
        $products = $this->getFilteredProductsQuery();
        $products->with('brand')
            ->has('brand', '=', 0)
            ->where(function($query) {
              $query->whereDoesntHave('aiGenerationHistory')
                    ->orWhereHas('aiGenerationHistory', function ($q) {
                              $q->where('extras->field', 'brand');
                          }, '<=', self::REQUEST_ATTEMPTS);
            })
            ->select('id', 'name');

        $chunks = $products->cursor()->chunk($this->chunk_size);
        $chunks_count = $chunks->count();

        $bar = $this->output->createProgressBar($chunks_count);
        $bar->start();

        foreach ($chunks as $index => $chunk) {
          if($this->chunk_limit && $index >= $this->chunk_limit) {
            break;
          }

            $productData = $chunk->map(function ($product) {
                $supplier = $product->suppliers()->first();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'supplier' => $supplier ? $supplier->name : null,
                    'price' => $supplier ? ($supplier->pivot->price ?? null) : null,
                    'barcode' => $supplier ? ($supplier->pivot->barcode ?? null) : null,
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

        $gh = AiGenerationHistory::createItem($product, ['field' => 'brand', 'ai' => $this->ai, 'model' => $this->model, 'temperature' => $this->temperature]);
        
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
          $this->error("Brand not present in response for product {$product->id}. Response: " . json_encode($item));
          $gh->updateStatus('error', "Brand not present in response");
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

    // private function getProductBrandsGrok($productData) {
    //   $prompt = $this->loadPromptFromFile('brands.txt');

    //   $user_data = [
    //       "products" => $productData,
    //       "brands" => Brand::getAvailableBrandsArray()
    //   ];

    //   $user_data_json = json_encode($user_data, JSON_UNESCAPED_UNICODE);


    //   // Initialize the client
    //   $config = new GrokConfig('xai-akWRPwtXID02V8cZO8p7Nm7znuloHqVlh79BQ0IxgT7dDGgstaQukJ9asIN3MMtf3q8m5Dk4HQPy5ZMC', null);
    //   $client = new GrokClient($config);

    //   // Define messages
    //   $messages = [
    //       ['role' => 'system', 'content' => $prompt],
    //       ['role' => 'user', 'content' => $user_data_json]
    //   ];

    //   // Call API
    //   $options = new ChatOptions(model: Model::GROK_2, temperature: 0.4, stream: false);
    //   $response = $client->chat($messages, $options);

    //   dd($response['choices'][0]['message']['content']);
    // }

    // private function getProductBrandsGemini($products) {
    //     $prompt = $this->loadPromptFromFile('brands.txt');
    //     $brands = Brand::getAvailableBrandsArray();

    //     // Формируем часть промпта с товарами
    //     $productsPart = "";
    //     foreach ($products as $product) {
    //         $productsPart .= "- id: {$product['id']}, name: {$product['name']}, supplier: {$product['supplier']}, price: {$product['price']} грн, barcode: {$product['barcode']}\n";
    //     }

    //     // Формируем часть промпта с брендами
    //     $brandsPart = "";
    //     foreach ($brands as $brand) {
    //         $brandsPart .= "- id: {$brand['id']}, name: {$brand['name']}\n";
    //     }


    //     $combinedPrompt = $prompt . "\n\n" .
    //         "Products:\n" . $productsPart . "\n\n" .
    //         "Brands:\n" . $brandsPart;

    //     // dd($combinedPrompt);

    //     $gemini = Gemini::client('AIzaSyDWhLqQaeIcJysvgaHsKJuGs1SRb11cHng');

    //     $generationConfig = new GenerationConfig(
    //       temperature: 0,
    //     );

    //     $result = $gemini
    //                 // ->geminiPro()
    //                 ->geminiFlash()
    //                 ->withGenerationConfig($generationConfig)
    //                 ->generateContent($combinedPrompt);

    //     $response = $result->text();

    //     dd($response);

    //     return $this->extractJsonFromOpenAiResponse($response);
    // }
}
