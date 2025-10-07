<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Models\Region;
use App\Http\Resources\RegionLargeResource;

class RegionController extends \App\Http\Controllers\Controller
{ 
  // public function index(Request $request) {

  //   $is_root = $request->input('is_root', true);
  //   $is_active = $request->input('is_active', true);

  //   $categories = Category::query()
  //             ->select('ak_product_categories.*')
              
  //             ->distinct('ak_product_categories.id')
              
  //             ->when($is_root, function($query) {
  //               $query->root();
  //             })

  //             ->when($is_active, function($query) {
  //               $query->active();
  //             })

  //             // Filter by extras field
  //             ->when($request->input('extras'), function($query) {
  //               $extras = $request->input('extras');
  //               foreach($extras as $key => $value) {
  //                 $value = is_numeric($value)? floatval($value): $value;
  //                 $query->whereJsonContains("extras->{$key}", $value);
  //               }
  //             })
              
  //             ->orderBy('lft')

  //             ->get();
    
  //   // default resource
  //   $resource = self::$resources['category']['small'];

  //   if($request->input('resource')) {
  //     $resource = self::$resources['category'][$request->input('resource')];
  //   }

  //   $categories = $resource::collection($categories);

  //   return $categories;
  // }

  public function show(Request $request, $slug) {
    $region = Region::where('slug', $slug)->first();
    $resource = new RegionLargeResource($region);
    return $resource;
  }
}
