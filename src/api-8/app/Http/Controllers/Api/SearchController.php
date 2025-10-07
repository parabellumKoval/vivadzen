<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

// use App\Http\Resources\ProductSmallResource;
use App\Http\Resources\ProductSearchResource;
use App\Http\Resources\CategorySearchResource;
use App\Http\Resources\BrandSearchResource;

use Backpack\Store\app\Http\Resources\ProductCollection;

class SearchController extends \App\Http\Controllers\Controller
{  
  
  /**
   * livesearch
   *
   * @param  mixed $request
   * @return void
   */
  public function livesearch(Request $request) {
    
    $per_page = request('perPage', 3);

    $products = Product::search(request('search'))->paginate(6);
    $categories = Category::search(request('search'))->paginate(20);
    $brands = Brand::search(request('search'))->paginate(20);

    return [
      'products' => ProductSearchResource::collection($products),
      'categories' => CategorySearchResource::collection($categories),
      'brands' => BrandSearchResource::collection($brands)
    ];
  }
  
  /**
   * index
   *
   * @param  mixed $request
   * @return void
   */
  public function index(Request $request) {
    $per_page = request('perPage', 20);
    $order_by = request('order_by', null);
    $order_dir = request('order_dir', null);

    // Categories
    // if(Cache::has('search_cats')) {
    //   $categories = Cache::get('search_cats');
    // }else {
    //   $categories = Category::search(request('search'))->get();
    //   Cache::put('search_cats', $categories, now()->addMinutes(60));
    // }
    // $categories = Category::search(request('search'))->get();
    // $categories_tree = $categories && count($categories)? $this->getCategoryTree($categories): [];

    // Products
    if($order_by === 'price' && $order_dir === 'asc') {
      $products = Product::search(request('search'))
                    ->within('ak_product_price_asc')
                    ->paginate($per_page);
    }else if($order_by === 'price' && $order_dir === 'desc') {
      $products = Product::search(request('search'))
                    ->within('ak_product_price_desc')
                    ->paginate($per_page);
    }else {
      $products = Product::search(request('search'))
                    ->paginate($per_page);
    }

    // dd($products);
    // $products = Product::search(request('search'))->orderBy('in_stock', 'DESC')->get();

    // Brands
    // $brands = Brand::search(request('search'))->get();

    return response()->json([
      'products' => new ProductCollection($products, [
        'resource_class' => 'App\Http\Resources\ProductSmallResource'
      ]),
      // 'categories' => CategorySearchResource::collection($categories_tree),
      // 'brands' => BrandSearchResource::collection($brands)
    ]);
  }

  public function collectRelations() {

  }
  
  /**
   * getCategoryTree
   *
   * @param  mixed $categories
   * @return void
   */
  public function getCategoryTree($categories) {
    $list = $this->getCategoryList($categories); 

    // dd($list->toArray()[0]['children']);
    $grouped = $this->groupTree($list);
    // dd($grouped[1]->children);
    
    // dd($grouped[0]->children[0]->children);
    return $grouped;    
  }
  
  /**
   * groupTree
   *
   * @param  mixed $categories
   * @return void
   */
  public function groupTree($categories) {
    $arr = [];
    
    $groups = $categories->groupBy('id');

    foreach($groups as $key => $group) {
      
      $title_category = $group[0];
      $childrens = collect();

      foreach($group as $k => $cat){
        if($cat->children) {
          if(isset($cat->children->id)) {
            $childrens->push($cat->children);
          }else {
            $childrens = $childrens->concat($cat->children);
          }
        }
      }

      $grouped_childrens = $this->groupTree($childrens);
      $title_category->setRelation('children', $grouped_childrens);
      $arr[] = $title_category;
    }

    return $arr;
  }
  
  /**
   * getCategoryList
   *
   * @param  mixed $categories
   * @return void
   */
  public function getCategoryList($categories) {
    $category_list = collect();
    $cats_clone = clone $categories;

    $i = 0;
    do{
      $category_array = $categories->splice(0, 1);
      $category = $category_array->first()->setRelation('children', null);
    
      $parent = $category->parent;
      
      if($parent) {
        $family = $this->getFamily($category);
        $category_list->push($family);
      }else {
        $category_list->push($category);
      }

    }while(count($categories) > 0);


    // print_r($cats_clone->pluck('id'));
    // echo '<br>';

    // foreach($category_list as $c) {
    //   echo $c->id . ' - ' . ($c->children->id ?? '-') . ' - ' . ($c->children->children->id ?? '-') . '<br>';
    // }

    // dd($cats_clone->pluck('id'));
    return $category_list;
  }
  
  /**
   * getFamily
   *
   * @param  mixed $category
   * @return void
   */
  public function getFamily($category) {
    $parent = $category->parent;

    if($parent) {
      $parent->setRelation('children', $category);
      return $this->getFamily($parent);
    }else {
      return $category;
    }
  }

  
}