<?php

namespace App\Console\Commands\Openai\Traits;

use App\Models\Product;

trait AiProductTrait {

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
}