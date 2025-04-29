<?php

namespace App\Console\Commands\Openai;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use App\Models\Category;
use Backpack\Store\app\Models\MerchantCategory;

// use Gemini;
// use Gemini\Data\GenerationConfig;

// use GrokPHP\Client\Clients\GrokClient;
// use GrokPHP\Client\Config\GrokConfig;
// use GrokPHP\Client\Config\ChatOptions;
// use GrokPHP\Client\Enums\Model;
// use GrokPHP\Client\Enums\DefaultConfig;

use App\Models\Libretranslate;

class SetMerchantToCategories extends BaseAi
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openai:set-merchant-to-categories';

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


    const CATEGORY_CHUNK_SIZE = 30;

    const TEST_CHUNK_LIMITS = null;

    // Google Merchant key = 469, name = Health & Beauty
    const DEFAULT_MERCHANT_ID = 2706;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      $response = $this->fillCategories();
    }

 
    private function fillCategories() {
        // Start with filtered query based on settings
        $categories = Category::getHasNotMerchantCategoriesArray('ru');

        $chunks = array_chunk($categories, self::CATEGORY_CHUNK_SIZE);
        $chunks_count = count($chunks);

        $bar = $this->output->createProgressBar($chunks_count);
        $bar->start();

        foreach ($chunks as $index => $chunk) {
            if(self::TEST_CHUNK_LIMITS && $index >= self::TEST_CHUNK_LIMITS) {
                break;
            }

            $en_categories = $this->translateCategories($chunk);
            
            $response = $this->setMerchantWithAi($en_categories);

            if(!$response) {
                $this->error('No response from OpenAI');
                continue;
            }

            // Process response and update product categories
            $this->updateCategories($response);

            $bar->advance();
        }

        $bar->finish();
    }
    
    /**
     * Method translateCategories
     *
     * @param $categories $categories [explicite description]
     *
     * @return void
     */
    private function translateCategories($categories) {
      return array_map(function($category) {
        $response = Libretranslate::translate($category['name'], 'ru', 'en');
        
        if($response['success'] === true) {
          $category['name'] = $response['translated'];
        }

        return $category;
      }, $categories);
    }
    
    /**
     * Method setMerchantWithAi
     *
     * @param $productData $productData [explicite description]
     *
     * @return void
     */
    private function setMerchantWithAi($categories) {
      $prompt = $this->loadPromptFromFile('merchants.txt');

      $user_data = [
          "merchant_categories" => $this->getMerchantCategoriesArray(),
          "categories" => $categories
      ];

      $user_data_json = json_encode($user_data, JSON_UNESCAPED_UNICODE);

      $response = $this->client->chat()->create([
          'model' => 'gpt-4o',
          'temperature' => 0.4,
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
     * Method getMerchantCategoriesArray
     *
     * @return void
     */
    private function getMerchantCategoriesArray() {
      // Путь к файлу в папке Files
      $filePath = __DIR__ . '/Files/' . 'merchant.txt';

      if (!file_exists($filePath)) {
          $this->error("File not found: {$filePath}");
          return 1;
      }

      $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      $result = [];

      foreach ($lines as $line) {
          // Пропускаем строки с заголовком
          if (str_starts_with($line, '#')) {
              continue;
          }

          // Разделяем строку на id и name
          $parts = explode(' - ', $line, 2);
          if (count($parts) !== 2) {
              $this->warn("Invalid line format: {$line}");
              continue;
          }

          $id = trim($parts[0]);
          $name = trim($parts[1]);

          // Добавляем в массив результат
          $result[] = [$id, $name];
      }

      // Вывод результата (для примера)
      // $this->info('Processed taxonomy:');
      // $this->table(['ID', 'Name'], $result);

      // Можно вернуть массив для дальнейшего использования
      return $result;

    }

    
    /**
     * Method updateCategories
     *
     * @param $data $data [explicite description]
     *
     * @return void
     */
    public function updateCategories($data) {

      if(!is_array($data)) {
        return;
      }

      foreach($data as $item) {
        $category = Category::find($item['c_id']);
        $merchantCategory = MerchantCategory::where('key', $item['m_id'])->first();
        
        if(!$category) {
          $this->error('No category found with id: ' . $item['c_id']);
          continue;
        }

        try {
          $category->merchant_id = $merchantCategory? $merchantCategory->id: self::DEFAULT_MERCHANT_ID;
          $category->save();
          $this->info('Category was updated: ' . $category->id);
        }catch(\Exception $e) {
          $this->error('Cant update category: ' . $e->getMessage());
        }
      }
    }
}
