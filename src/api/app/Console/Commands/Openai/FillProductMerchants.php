<?php

namespace App\Console\Commands\Openai;

// use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Models\Product;
use App\Models\AiGenerationHistory;
use App\Exceptions\HistoryException;

use OpenAI;

class FillProductMerchants extends BaseAi
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openai:fill-product-merchants {ai?} {model?} {temperature?} {chunk_limit?} {chunk_size?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    const TEST_CHUNK_LIMITS = null;
    const CHUNK_SIZE = 150;
    const REQUEST_ATTEMPTS = 2;


    const DEFAULT_AI = 'gpt';
    const DEFAULT_MODEL = 'gpt-4.1';
    const DEFAULT_TEMP = 0.7;
    

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

        $this->fillMerchant();
    }


    /**
     * fillMerchant
     *
     * @return void
     */
    private function fillMerchant() {
        // Start with filtered query based on settings
        $products = $this->getFilteredProductsQuery();

        $products->has('brand')
                ->where('merchant_content', null)
                ->where(function($query) {
                    $query->whereDoesntHave('aiGenerationHistory')
                          ->orWhereHas('aiGenerationHistory', function ($q) {
                                    $q->where('extras->field', 'merchant')
                                        ->whereIn('status', ['error', 'pending']);
                                }, '<=', self::REQUEST_ATTEMPTS);
                  });

        $chunks = $products->cursor()->chunk($this->chunk_size);
        $chunks_count = $chunks->count();

        $bar = $this->output->createProgressBar($chunks_count);
        $bar->start();

        foreach($chunks as $index => $chunk) {

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

            $gh = AiGenerationHistory::createItem($product, ['field' => 'merchant', 'ai' => $this->ai, 'model' => $this->model, 'temperature' => $this->temperature]);
            
            try {
                $responce = $this->getProductMerchantContent($productData, 'uk');
                $this->updateProductsMerchantContent($responce);

                $gh->updateStatus('done', '');
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
     * Method updateProductsMerchantContent
     *
     * @param $data $data [explicite description]
     *
     * @return void
     */
    private function updateProductsMerchantContent($data) {
        if(empty($data) || !is_array($data)) {
            throw new \Exception('Data is not array.');
        }

        foreach ($data as $index => $item) {

            if(!isset($item['p_id']) || empty($item['p_id'])) {
                $this->error('No product id in data item. Skipping..');
                continue;
            }

            if(!isset($item['content']) || empty($item['content'])) {
                $this->error('No content in data item. Skipping..');
                continue;
            }

            $product = Product::find($item['p_id']);
            if(!$product) {
                $this->error('Skipping.. Cant find product with id: ' . $item['p_id']);
                continue;
            }

            $product->setTranslation('merchant_content', 'uk', $item['content']);
            $product->is_ai_merchant_content = 1;
            $product->save();

            $this->info('Updated product merchant content: ' . $product->id);
        }
    }
    /**
     * openaiCall
     *
     * @return void
     */
    private function getProductMerchantContent($products_array = null, string $lang = 'ru') {
        $prompt = $this->loadPromptFromFile('merchants-content.txt');
     
        $user_data = [
            "products" => $products_array,
            'lang' => $lang
        ];

        $user_data_json = json_encode($user_data, JSON_UNESCAPED_UNICODE);

        $response = $this->client->chat()->create([
            'temperature' => $this->temperature,
            'model' => $this->model,
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
}