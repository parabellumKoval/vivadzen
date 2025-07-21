<?php

namespace App\Console\Commands\Openai;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use Illuminate\Support\Facades\Http;

use App\Models\Product;
use App\Models\Category;
use App\Models\Bunny;
use App\Models\Libretranslate;


use Backpack\Settings\app\Models\Settings;

use OpenAI;
use App\Console\Commands\Openai\Traits\CategoryImage;

class SearchImages extends Command
{
    use CategoryImage;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:search {--limit=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update catalog cache';

    protected $client = null;

    private $available_languages = [];

    private $langs_list = [];

    private $api_key = null;

    protected $settings = null;

    protected $opts = null;

    public $product_items_limit = 2;

    const MIN_PRODUCT_IMAGE_WIDTH = 500;
    const MIN_PRODUCT_IMAGE_HEIGHT = 500;


    const MIN_CATEGORY_IMAGE_WIDTH = 100;
    const MIN_CATEGORY_IMAGE_HEIGHT = 100;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
      parent::__construct();

      // available languages
      $this->available_languages = config('backpack.crud.locales');
      $this->langs_list = array_keys($this->available_languages);

      // serper api
      $this->api_key = config('serper.api_key');

    }


    // public function findNicestImages() {
      
    //   $items = json_decode($data, true);
    //   $images_data = $items['images'];

    //   $mapped_images = array_map(function($item) {
    //       $imageUrl = $item['imageUrl'];
      
    //       // Загружаем изображение из URL
    //       $imageContent = @file_get_contents($imageUrl);
    //       if ($imageContent === false) {
    //           return null; // пропускаем, если не удалось загрузить
    //       }
      
    //       // Создаем изображение из строки
    //       $image = @imagecreatefromstring($imageContent);
    //       if (!$image) {
    //           return null;
    //       }
      
    //       $origWidth = imagesx($image);
    //       $origHeight = imagesy($image);
      
    //       // Определим новый размер — максимум 500px по ширине, сохраняя пропорции
    //       $maxWidth = 500;
    //       if ($origWidth > $maxWidth) {
    //           $scale = $maxWidth / $origWidth;
    //           $newWidth = $maxWidth;
    //           $newHeight = intval($origHeight * $scale);
    //       } else {
    //           $newWidth = $origWidth;
    //           $newHeight = $origHeight;
    //       }
      
    //       // Создаем новое изображение и копируем сжатое содержимое
    //       $resized = imagecreatetruecolor($newWidth, $newHeight);
    //       imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
      
    //       // Сохраняем сжатое изображение в память
    //       ob_start();
    //       imagejpeg($resized, null, 85); // 85 — компромисс между качеством и размером
    //       $jpegData = ob_get_clean();
      
    //       // Кодируем в base64
    //       $base64 = 'data:image/jpeg;base64,' . base64_encode($jpegData);
      
    //       // Очищаем память
    //       imagedestroy($image);
    //       imagedestroy($resized);
      
    //       return [
    //           'title' => $item['title'],
    //           'imageUrl' => $item['imageUrl'],
    //           'imageWidth' => $item['imageWidth'],
    //           'imageHeight' => $item['imageHeight'],
    //           'imageBase64' => $base64,
    //       ];
    //   }, $images_data);
    // }



    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      // Settings
      $this->settings = Settings::where('key', 'image_generation_settings')->first();

      if (!$this->settings) {
          $this->error('Image generation settings not found.');
          return 1;
      }else {
        $this->opts = $this->settings->extras;
      }

      // $this->product_items_limit = (int)$this->option('limit');
      // $this->category_items_limit = (int)$this->option('limit');

      // if (!isset($this->opts['auto_generation_enabled']) || !$this->opts['auto_generation_enabled']) {
      //     $this->info('Auto image loading is disabled in settings. Exiting.');
      //     return;
      // }

      if (isset($this->opts['generate_for_products']) && $this->opts['generate_for_products']) {
        $this->autoloadProductImages();
      }else {
        $this->info('Auto image loading for Products is disabled in settings. Exiting.');
      }

      // if(isset($this->opts['generate_for_categories']) && $this->opts['generate_for_categories']) {
      //   $this->autoloadCategoryImages();
      // }else {
      //   $this->info('Auto image loading for Categories is disabled in settings. Exiting.');
      // }

    }

    /**
     * Method autoloadProductImages
     *
     * @return void
     */
    private function autoloadProductImages() {

      $images_limit = isset($this->opts['product_images_count']) && $this->opts['product_images_count'] > 0? $this->opts['product_images_count']: 0;

      if(!$images_limit) {
        $this->info('Product images limit is not set in settings or equal to 0. Exiting.');
        return;
      }

      $is_only_active = $this->opts['active_products_only'] ?? true;
      $is_only_in_stock = $this->opts['in_stock_products_only'] ?? true;
      $min_price = $this->opts['min_price'] ?? 0;

	    $products = Product::where(function($query) {
        $query->where('images', null);
        $query->orWhereRaw('JSON_LENGTH(images) = ? ', 0);
      })
      ->when($is_only_active, function($query) {
        $query->where('is_active', 1);
      })
      ->when($is_only_in_stock, function($query) {
        $query->whereHas('sp', function($query) {
          $query->where('in_stock', '>', 0);
        });
      })
      ->when($min_price, function($query) use ($min_price) {
        $query->whereHas('sp', function($query) use ($min_price) {
          $query->where('price', '>=', $min_price);
        });
      });

      $products_count = $products->count();
      $products_cursor = $products->cursor();

      $bar = $this->output->createProgressBar($products_count);
      $bar->start();

      foreach($products_cursor as $index => $product) {
        if ($this->product_items_limit !== null && $index >= $this->product_items_limit) break;

        $query = $this->getProductSearchQuery($product);
        
        dd($query);
        if(!$query) {
          continue;
        }

        $images = $this->findImages($query, $images_limit, self::MIN_PRODUCT_IMAGE_WIDTH, self::MIN_PRODUCT_IMAGE_HEIGHT);

        if(!$images) {
          continue;
        }

        $images_array = [];

        foreach($images as $image) {
          $images_array[] = [
            'src' => $image['imageUrl'],
            'alt' => null,
            'title' => null,
          ];
        }

        $product->images = $images_array;
        $product->is_images_generated = 1;
        $product->save();

        $this->info('Product ' . $product->id . ' - https://djini.com.ua/' . $product->slug  . ' processed');
        $bar->advance();
      }

      $bar->finish();
    }

       
        
    /**
     * Method getProductSearchQuery
     *
     * @param $product $product [explicite description]
     *
     * @return void
     */
    private function getProductSearchQuery($product) {
      $brand = $product->brand;

      if(!$brand) {
        $this->info('Product ' . $product->id . ' - ' . $product->slug  . ' has no brand. Skipping.');
        return null;
      }

      if(!$brand->website) {
        $this->info('Product ' . $product->id . ' - ' . $product->slug  . ' has no brand website. Skipping.');
        return null;
      }

      $website = $brand->website;
      $website = preg_replace('/^https?:\/\//', '', $website);

      $ru_name = $product->getTranslation('name', 'ru');
      $uk_name = $product->getTranslation('name', 'uk');

      if(!empty($ru_name)) {
        $latin_full_name = $this->processProductName($ru_name, $brand->name);
      }else if(!empty($uk_name)) {
        $latin_full_name = $this->processProductName($uk_name, $brand->name);
      }else {
        $this->info('Product ' . $product->id . ' - ' . $product->slug  . ' has no name in ru or uk. Skipping.');
        return null;
      }

      $latin_full_name = $latin_full_name['final_name'];
      $query = "{$latin_full_name} site:{$website}";

      return $query;
    }

    
    /**
     * Method processProductName
     *
     * @param string $productName [explicite description]
     * @param string $brand [explicite description]
     *
     * @return array
     */
    public function processProductName(string $productName, string $brand): array
    {
        // 1. Извлечь только латинский текст (включая скобки, тире, пробелы и цифры)
        preg_match_all('/[A-Za-z0-9(),.\- ]+/', $productName, $matches);
        $latinOnly = trim(implode(' ', $matches[0]));

        // Удалить лишние пробелы
        $latinOnly = preg_replace('/\s+/', ' ', $latinOnly);

        // 2. Проверить наличие бренда в латинском фрагменте
        if (stripos($latinOnly, $brand) === false) {
            $finalName = trim($brand . ' ' . $latinOnly);
        } else {
            $finalName = trim($latinOnly);
        }

        return [
            'latin_text' => $latinOnly,
            'final_name' => $finalName
        ];
    }

    /**
     * Method findImages
     *
     * @param $query $query [explicite description]
     * @param $min_width $min_width [explicite description]
     * @param $min_height $min_height [explicite description]
     *
     * @return void
     */
    private function findImages($query, $limit, $min_width = 100, $min_height = 100) {
      $url = 'https://google.serper.dev/images';
      
      $headers = [
        'X-API-KEY' => $this->api_key,
        'Content-Type' => 'application/json'
      ];

      $body = [
        "q" => $query,
        "gl" => "ua",
        "hl" => "uk"
      ];

      try {
        $response = Http::withHeaders($headers)->post($url, $body);
        $data = $response->json();

        if(!isset($data['images']) || !is_array($data['images'])) {
          throw new Exception('No serper search results');
        }

        $filtered_images = array_filter($data['images'], function($item) use ($min_width, $min_height) {
          if($item['imageWidth'] >= $min_width && $item['imageHeight'] >= $min_height) {
            return true;
          }else {
            return false;
          }
        });

        $images = !empty($filtered_images)? array_slice($filtered_images, 0, $limit): null;
      }catch(\Exception $e) {
        \Log::error($e->getMessage());
        return null;
      }
      
      return $images;
    }

}
