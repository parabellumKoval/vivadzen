<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use Backpack\Store\app\Models\Category;
use App\Http\Resources\CategorySlugResource;

class CategoryController extends \App\Http\Controllers\Controller
{
  use \Backpack\Store\app\Traits\Resources;

  public function productOrCategory(Request $request, $slug) {
    self::resources_init();

    $this->product_class = config('backpack.store.product.class', 'Backpack\Store\app\Models\Product');
    $product = $this->product_class::where('slug', $slug)->first();

    if($product) {
      $product_resource = new self::$resources['product']['large']($product);
      return response()->json($product_resource);
    }

    $category = Category::where('slug', $slug)->firstOrFail();
    $category_resource = new self::$resources['category']['large']($category);
    return response()->json($category_resource);
  }
  
  public function getSlugs(Request $request) {
    
    $categories = Category::query()
      ->select('ak_product_categories.*')
      ->distinct('ak_product_categories.id')
      ->active()
      ->orderBy('lft')
      ->get();
    
    $categories = CategorySlugResource::collection($categories);

    return $categories;
  }
}