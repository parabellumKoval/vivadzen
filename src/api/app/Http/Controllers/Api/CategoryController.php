<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;


use App\Models\Region;
use Backpack\Store\app\Models\Category;
use App\Http\Resources\CategorySlugResource;

use Illuminate\Support\Facades\Cache;

class CategoryController extends \App\Http\Controllers\Controller
{
  use \Backpack\Store\app\Traits\Resources;
  
  public function __construct() {
    self::resources_init();
  }

  /**
   * productOrCategory
   *
   * @param  mixed $request
   * @param  mixed $slug
   * @return void
   */
  public function productOrCategory(Request $request, $slug) {
    $page = $request->input('page', null);

    // Cache::forget('category-data-'.$slug);
    // 1) TRY GET CASHED CATALOG DATA
    if(Cache::has('category-data-' . $slug)) {
      $cached_data = Cache::get('category-data-' . $slug);

      if(!empty($page) && $page > 1) {
        $cached_data = $this->updateProductsData($request, $cached_data, $slug);
      }

      return response()->json($cached_data);
    }

    // 2) THEN TRY FIND PRODUCT
    $product = $this->getProduct($slug);
    if($product) return response()->json($product);

    // 3) If Not product then try get category and return catalog data (same 1 step)
    $category = Category::where('slug', $slug)->where('is_active', 1)->first();
   
    // if no category found try found in Regions
    if(!$category) {
      // Region is category analogue but by location
      $region = Region::where('slug', $slug)->where('is_active', 1)->first();
    }else {
      $region = null;
    }

    if(!empty($category) || !empty($region)) {
      $all_data = $this->catalogData($request, $category, $region);
      Cache::put('category-data-' . $slug, $all_data);
      return response()->json($all_data);
    }
  }
    
  /**
   * getProduct
   *
   * @param  mixed $slug
   * @return void
   */
  public function getProduct($slug) {
    $this->product_class = config('backpack.store.product.class', 'Backpack\Store\app\Models\Product');
    $product = $this->product_class::where('slug', $slug)->where('is_active', 1)->first();

    // --- return the product
    if($product) {
      $product_resource = new self::$resources['product']['large']($product);
      return $product_resource;
    }else {
      return null;
    }
  }
  
  /**
   * getSlugs
   *
   * @param  mixed $request
   * @return void
   */
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
    
  /**
   * updateProductsData
   *
   * @param  mixed $request
   * @param  mixed $data
   * @param  mixed $slug
   * @return void
   */
  private function updateProductsData(Request $request = null, $data, $slug = null) {
    if(get_class($data['category']) === 'App\Http\Resources\RegionLargeResource') {
      $slug = $data['category']->category->slug;
    }else {
      $slug = $data['category']->slug;
    }

    $slug = $slug? $slug: $request->input('category_slug');

    $fake_request = new \Illuminate\Http\Request();
    $fake_request->replace([
      'category_slug' => $slug,
      'per_page' => 20,
      'page' => $request->input('page')
    ]);

    $product_controller = new \Backpack\Store\app\Http\Controllers\Api\ProductController();
    // First page products and all filters meta
    $products = $product_controller->index($fake_request, false);

    $data['products'] = $products['products'];

    return $data;
  }

  /**
   * catalogData
   *
   * @param  mixed $request
   * @param  mixed $slug
   * @return void
   */
  public function catalogData(Request $request = null, $category = null, $region = null) {
    // $slug = $slug? $slug: $request->input('category_slug');
    if($category) {
      $slug = $category->slug;
    }else if($region) {
      $slug = $region->category->slug;
    }else {
      $slug = $request->input('category_slug');
    }

    $fake_request = new \Illuminate\Http\Request();
    $fake_request->replace([
      'category_slug' => $slug,
      'per_page' => 20
    ]);

    $product_controller = new \Backpack\Store\app\Http\Controllers\Api\ProductController();
    // First page products and all filters meta
    $products_page_1 = $product_controller->index($fake_request, false);

    // Brands
    $fake_request->replace([
      'category_slug' => $slug,
      'per_page' => 20000
    ]);
    $brand_controller = new \Backpack\Store\app\Http\Controllers\Api\ProductController();
    $brands = $brand_controller->brands($fake_request, false);


    // Category
    if($category) {
      $category_data = new self::$resources['category']['large']($category);
    }
    // else {
    //   $category_controller = new \Backpack\Store\app\Http\Controllers\Api\CategoryController;
    //   $category_data = $category_controller->show($fake_request, $slug);
    // }

    // Region
    if($region) {
      $category_data = new \App\Http\Resources\RegionLargeResource($region);
    }
    // else {
    //   $category_controller = new \App\Http\Controllers\Api\RegionController;
    //   $category_data = $category_controller->show($fake_request, $slug);
    // }

    // Attributes
    $attributes_controller = new \Backpack\Store\app\Http\Controllers\Api\AttributeController;
    $attributes = $attributes_controller->index($fake_request, false);

    // Reviews
    $fake_request->replace([
      'category_slug' => $slug,
      'per_page' => 6,
      'resource' => 'large',
      'with_text' => 1
    ]);

    $review_controller = new \App\Http\Controllers\Api\ReviewController;
    $reviews = $review_controller->index($fake_request);
    // dd($reviews);

    //CHIP Products
    // category_id=3&price[0]=5&price[1]=100000000&order_by=price&order_dir=ASC&selections[0]=in_stock&per_page=5&with_filters=0
    $fake_request->replace([
      'category_slug' => $slug,
      'price' => [5, 100000000],
      'order_by' => 'price',
      'order_dir' => 'ASC',
      'selections' => ['in_stock'],
      'per_page' => 5,
      'with_filters' => 0
    ]);

    $chip_products = $product_controller->index($fake_request, false);

    //POPULAR Products
    //category_id=3&order_by=sales&order_dir=DESC&selections[0]=in_stock&per_page=5&with_filters=0
    $fake_request->replace([
      'category_slug' => $slug,
      'price' => [5, 100000000],
      'order_by' => 'sales',
      'order_dir' => 'DESC',
      'selections' => ['in_stock'],
      'per_page' => 5,
      'with_filters' => 0
    ]);

    $popular_products = $product_controller->index($fake_request, false);

    return [
      'products' => $products_page_1['products'] ?? null,
      'filters' => $products_page_1['filters'] ?? null,
      'brands' => $brands,
      'category' => $category_data,
      'attributes' => $attributes,
      'reviews' => $reviews ?? null,
      'chip_products' => $chip_products['products'] ?? null,
      'popular_products' => $popular_products['products'] ?? null
    ];
  }
}