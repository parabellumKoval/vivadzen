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

    $products = Product::search(request('search'))->paginate($per_page);
    $categories = Category::search(request('search'))->paginate($per_page);
    $brands = Brand::search(request('search'))->paginate($per_page);

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
    $products = Product::search(request('search'))->paginate($per_page);
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

  public function getCategoryTree($categories) {
    $list = $this->getCategoryList($categories); 

    // dd($list->toArray()[0]['children']);
    $grouped = $this->groupTree($list);
    // dd($grouped[1]->children);
    
    // dd($grouped[0]->children[0]->children);
    return $grouped;    
  }

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

  public function getFamily($category) {
    $parent = $category->parent;

    if($parent) {
      $parent->setRelation('children', $category);
      return $this->getFamily($parent);
    }else {
      return $category;
    }
  }

  // public function getFamily($category) {
  //   if($category->parent) {
  //     $parent = $this->getFamily($category->parent);
  //     $parent = $this->setDeepestChildren($parent, $category);
  //     return $parent;
  //   }else {
  //     $category->setRelation('children', null);
  //     return $category;
  //   }
  // }

  public function setDeepestChildren($category, $children) {
    if($category->children && $category->children->first()) {
      echo 'setDeepestChildren - ' . $category->children->first()->id . ' - ' . $children->id;
      echo '<br>';
      $this->setDeepestChildren($category->children->first(), $children);
    }else {
      // $childrens = $categories->where('parent_id', $category->id)->all();
      // $searched_keys = array_keys($childrens);

      // foreach($searched_keys as $k){
      //   $categories->forget($k);
      // }

      // $node = collect(array_values($childrens))->push($children);
      // $category->setRelation('children', $node);
      // if($children->id) {
      //   echo 'category - ';
      // }else {
      //   echo 'collection - ';
      // }

      $category->setRelation('children', $children);
    }

    return $category;
  }

  // public function createTree($categories) {
  //   $tree = [];

  //   $category_array = $categories->splice(1, 1);
  //   $category = $category_array->first();

  //   [$children, $not_children] = $this->intersectChildren($category, $categories);

  //   if($children) {
  //     $category->setRelation('children', $children);
  //     dd($category);
  //   }
    
  //   if($not_children) {
  //     $this->createTree($not_children);
  //   }
  // }

  // public function intersectChildren($category, $list) {
  //   $children = [];
  //   $not_children = [];

  //   for($i = 0; $i < count($list); $i++) {
  //     if($list[$i]->parent_id === $category->id){
  //       $children[] = $list[$i];
  //     }else {
  //       $not_children[] = $list[$i];
  //     }
  //   }

  //   return [
  //     $children,
  //     $not_children
  //   ];
  // }

  // public function getCategoryList($categories) {
  //   $category_list = collect();

  //   for($i = 0; $i < count($categories); $i++) {
  //     $category = $categories[$i];

  //     $parent = $category->parent;
      
  //     if($parent) {
  //       $family = $this->getFamily($category);
  //       $category_list = $category_list->concat($family);
  //     }else {
  //       $category_list->push($category);
  //     }
  //   }

  //   return $category_list->unique('id')->values();
  // }

  // public function getFamily($category, $family = null) {
  //   if(!$family) {
  //     $family = collect();
  //   }

  //   $family->push($category);

  //   if($category->parent) {
  //     return $this->getFamily($category->parent, $family);
  //   }

  //   return $family;
  // }
  
}