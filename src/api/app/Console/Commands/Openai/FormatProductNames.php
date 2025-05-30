<?php

namespace App\Console\Commands\Openai;

// use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Models\Product;
use App\Models\AiGenerationHistory;


class FormatProductNames extends BaseAi
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openai:format-product-names {ai?} {model?} {temperature?} {chunk_limit?} {chunk_size?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update catalog cache';

    const NAME_CHUNK_SIZE = 50;
    const TEST_CHUNK_LIMITS = null;
    const REQUEST_ATTEMPTS = 2;

    const DEFAULT_AI = 'gpt';
    const DEFAULT_MODEL = 'gpt-4.1';
    const DEFAULT_TEMP = 0.8;
    

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

      $this->chunk_size = $this->argument('chunk_size') ?? self::NAME_CHUNK_SIZE;
      $this->chunk_limit = $this->argument('chunk_limit') ?? self::TEST_CHUNK_LIMITS;

      $this->fillProductNames();
    }
    
     
    /**
     * Method getFilteredProductChunksForNames
     *
     * @return void
     */
    public function getFilteredProductChunksForNames()
    {
        // Функция фильтрации одного товара
        $filterProduct = function ($product) {
            // Исключаем полностью кириллицу с цифрами
            if (preg_match('/^[А-Яа-яЁёҐґЄєІіЇї0-9Xx\s\W]+$/', $product->name)) {
                return false;
            }
            // Исключаем кириллицу, затем латиницу, затем число
            if (preg_match('/[А-Яа-яЁёҐґЄєІіЇї].*[A-Za-z].*[0-9]/', $product->name)) {
                return false;
            }
            return true;
        };
    
        // Базовый запрос
        $query = Product::with('brand')
            ->where('is_active', 0)
            ->has('brand')
            ->where(function($query) {
                $query->whereDoesntHave('aiGenerationHistory')  // 1. Нет историй совсем
                    ->orWhere(function($q) {
                        $q->whereDoesntHave('aiGenerationHistory', function($h) {  // 2. Нет успешных историй
                            $h->where('extras->field', 'name')
                              ->where('status', 'done');
                        })
                        ->whereHas('aiGenerationHistory', function($h) {  // Либо есть только неуспешные в пределах лимита
                          $h->where('extras->field', 'name')
                            ->where('status', 'error');
                        }, '<=', self::REQUEST_ATTEMPTS);
                    });
            })
            ->select('id', 'name', 'brand_id');
    
        // Собираем отфильтрованные товары
        $filteredProducts = [];
        $cursor = $query->cursor(); // Используем cursor для экономии памяти
    
        foreach ($cursor as $product) {
            if ($filterProduct($product)) {
                $filteredProducts[] = [
                    'id' => $product->id,
                    'n' => $product->name,
                    'b' => $product->brand ? $product->brand->name : null
                ];
            }
        }
    
        // Формируем чанки
        $chunks = array_chunk($filteredProducts, $this->chunk_size);
    
        // Ограничиваем количество чанков, если задано
        if ($this->chunk_limit && is_numeric($this->chunk_limit)) {
            $chunks = array_slice($chunks, 0, $this->chunk_limit);
        }
    
        // Возвращаем коллекцию чанков
        return collect($chunks);
    }

    /**
     * Method fillProductNames
     *
     * @return void
     */
    private function fillProductNames() {

      $chunks = $this->getFilteredProductChunksForNames();
      $chunks_count = $chunks->count();

      $bar = $this->output->createProgressBar($chunks_count);
      $bar->start();

      foreach ($chunks as $index => $chunk) {
          $response = $this->getProductNames($chunk);

          if(!$response) {
              $this->error('No response from OpenAI');
              continue;
          }

          // Process response and update product categories
          $this->updateProductNames($response);

          $bar->advance();
      }

      $bar->finish();
    }
    
    /**
     * Method getProductNames
     *
     * @param $productData $productData [explicite description]
     *
     * @return void
     */
    private function getProductNames($productData) {
      $prompt = $this->loadPromptFromFile('names.txt');
      $user_data = [
          "products" => $productData,
          "lang" => 'uk'
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
     * Method updateProductNames
     *
     * @param $data $data [explicite description]
     *
     * @return void
     */
    private function updateProductNames($data) {
      if(empty($data) || !is_array($data)) {
        $this->error('No data to update product names');
        return;
      }

      foreach ($data as $item) {
        if(!isset($item['p_id']) || empty($item['p_id'])) {
          $this->error('No product id in data item. Skipping..');
          continue;
        }

        $product = Product::find($item['p_id']);
        if(!$product) {
          $this->error('Skipping.. Cant find product with id: ' . $item['p_id']);
          continue;
        }


        $gh = AiGenerationHistory::createItem($product, ['field' => 'name', 'ai' => $this->ai, 'model' => $this->model, 'temperature' => $this->temperature]);

        try {
          if(!isset($item['name']) || empty($item['name'])) {
            $message = 'No name in data item. Skipping..';
            $this->error($message);
            $gh->updateStatus('error', $message);
            continue;
          }

          // Save original name to "extras_trans" field
          $product->saveOriginalName();

          $names = [
            'ru' => null,
            'uk' => $item['name']
          ];

          $product->setTranslations('name', $names);
          $product->name_ai_generated = 1;
          $product->save();

          $gh->updateStatus('done', '');
          $this->info('Updated product id: ' . $product->id . ', name = ' . $item['name']);

        }catch(\Exception $e) {
            $gh->updateStatus('error', $e->getMessage());
            $this->error($e->getMessage());
        }

      }

    }
}
