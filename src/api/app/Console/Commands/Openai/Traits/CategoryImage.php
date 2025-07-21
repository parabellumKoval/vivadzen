<?php

namespace App\Console\Commands\Openai\Traits;

use App\Models\Product;
use App\Models\Category;
use App\Models\Bunny;
use App\Models\Libretranslate;

trait CategoryImage {

    public $category_items_limit = 2;

    /**
     * Method getCategorySearchQuery
     *
     * @param $category $category [explicite description]
     *
     * @return void
     */
    private function getCategorySearchQuery($category) {
      
      $category_ru_name = $category->getTranslation('name', 'ru');
      $category_uk_name = $category->getTranslation('name', 'uk');

      if(!empty($category_ru_name)) {
        $response = Libretranslate::translate($category_ru_name, 'ru', 'en');
      }elseif(!empty($category_uk_name)) {
        $response = Libretranslate::translate($category_uk_name, 'uk', 'en');
      }else {
        $this->info('Category ' . $category->id . ' - https://djini.com.ua/' . $category->slug  . ' has no name in ru or uk. Skipping.');
        return null;
      }

      if($response['success'] !== true) {
        \Log::error('Libretranslate error: ' . $response['error']);
        return null;
      }

      $query = "{$response['translated']} stock image free";
      return $query;
    }


    /**
     * Method autoloadCategoryImages
     *
     * @return void
     */
    private function autoloadCategoryImages() {
      $images_limit = isset($this->opts['category_images_count']) && $this->opts['category_images_count'] > 0? $this->opts['category_images_count']: 0;

      if(!$images_limit) {
        $this->info('Category images limit is not set in settings or equal to 0. Exiting.');
        return;
      }

      $is_only_active = $this->opts['active_categories_only'] ?? true;
      $is_only_with_products = $this->opts['categories_with_products_only'] ?? true;

      $categories = Category::where(function($query) {
        $query->whereNull('images')
              ->orWhereRaw('JSON_LENGTH(images) = 0')
              ->orWhere(function ($q) {
                  $q->whereRaw("
                      JSON_TYPE(images) = 'array' AND 
                      JSON_CONTAINS(
                          JSON_EXTRACT(images, '$'),
                          JSON_OBJECT('src', CAST(NULL AS JSON))
                      )
                  ");
              })
              ->orWhere(function ($q) {
                  $q->whereRaw("
                      JSON_TYPE(images) = 'array' AND 
                      JSON_CONTAINS(
                          JSON_EXTRACT(images, '$'),
                          JSON_OBJECT('src', '')
                      )
                  ");
              });
      })
      ->when($is_only_active, function($query) {
        $query->where('is_active', 1);
      })
      ->when($is_only_with_products, function($query) {
        $query->whereHas('products');
      });

      $categories_count = $categories->count();
      $categories_cursor = $categories->cursor();

      $bar = $this->output->createProgressBar($categories_count);
      $bar->start();

      foreach($categories_cursor as $index => $category) {
        if ($this->category_items_limit !== null && $index >= $this->category_items_limit) break;

        $query = $this->getCategorySearchQuery($category);
        
        if(!$query) {
          continue;
        }

        $images = $this->findImages($query, $images_limit, self::MIN_CATEGORY_IMAGE_WIDTH, self::MIN_CATEGORY_IMAGE_HEIGHT);

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

        $category->images = $images_array;
        $category->is_images_generated = 1;
        $category->save();

        $this->info('Category ' . $category->id . ' - ' . $category->slug  . ' processed');
        $bar->advance();
      }
      $bar->finish();
    }
}