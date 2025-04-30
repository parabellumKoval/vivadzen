<?php

namespace App\Console\Commands\Openai;

// use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Models\Product;
use App\Models\AiGenerationHistory;
use App\Exceptions\HistoryException;

use OpenAI;

class FillProductsMerchant extends BaseAi
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openai:fill-products-merchants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    const TEST_CHUNK_LIMITS = null;
    const PRODUCT_CHUNK_SIZE = 150;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->fillProductsMerchant();
    }
    /**
     * fillProductsContent
     *
     * @return void
     */
    private function fillProductsMerchant() {
        // Start with filtered query based on settings
        $products = Product::where('is_active', 1)
                                // ->whereHas('sp', function($q) {
                                //     $q->where('in_stock', '>', 0);
                                // })
                                ->has('brand')
                                ->where('merchant_content', null);

        $chunks = $products->cursor()->chunk(self::PRODUCT_CHUNK_SIZE);
        $chunks_count = $chunks->count();

        $bar = $this->output->createProgressBar($chunks_count);
        $bar->start();

        foreach($chunks as $index => $chunk) {

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
            // $gh = AiGenerationHistory::createItem($product, ['field' => 'content']);
            
            try {
                $responce = $this->getProductMerchantContent($productData, 'uk');
                $this->updateProductsMerchantContent($responce);

                // $gh->updateStatus('done', '');
            } catch(\Exception $e) {
                // $gh->updateStatus('error', $e->getMessage());
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
            'temperature' => 0.7,
            'model' => 'gpt-4.1',
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