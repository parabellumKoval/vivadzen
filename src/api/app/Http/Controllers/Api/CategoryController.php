<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use Backpack\Store\app\Models\Category;
use App\Http\Resources\CategoryTinyResource;

class CategoryController extends \App\Http\Controllers\Controller
{
  public function getSlugs(Request $request) {
    
    $categories = Category::query()
      ->select('ak_product_categories.*')
      ->distinct('ak_product_categories.id')
      ->active()
      ->orderBy('lft')
      ->get();
    
    $categories = CategoryTinyResource::collection($categories);

    return $categories;
  }
}