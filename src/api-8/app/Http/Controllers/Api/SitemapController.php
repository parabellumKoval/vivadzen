<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use Backpack\Store\app\Models\Category;
use Backpack\Store\app\Models\Product;

use App\Http\Resources\CategorySlugResource;

class SitemapController extends \App\Http\Controllers\Controller
{
    
  /**
   * getArticles
   *
   * @param  mixed $request
   * @return void
   */
  public function getArticles(Request $request) {
    $articles = \DB::table('ak_articles')
      ->select('ak_articles.slug')
      ->where('status', 'PUBLISHED')
      ->get();

    $articles_array = $articles->all();

    $articles_links = array_map(function($item) {
      return '/blog/' . $item->slug;
    }, $articles_array);

    $articles_links_ru = array_map(function($item) {
      return '/ru/blog/' . $item->slug;
    }, $articles_array);
    

    return [
      ...$articles_links,
      ...$articles_links_ru
    ];
  }

  /**
   * getBrands
   *
   * @param  mixed $request
   * @return void
   */
  public function getBrands(Request $request) {
    $brands = \DB::table('ak_brands')
      ->select('ak_brands.slug')
      ->where('is_active', 1)
      ->get();

    $brands_array = $brands->all();

    $brands_links = array_map(function($item) {
      return '/brands/' . $item->slug;
    }, $brands_array);

    $brands_links_ru = array_map(function($item) {
      return '/ru/brands/' . $item->slug;
    }, $brands_array);
    

    return [
      ...$brands_links,
      ...$brands_links_ru
    ];
  }
    
  /**
   * getProducts
   *
   * @param  mixed $request
   * @return void
   */
  public function getProducts(Request $request) {
    $products = \DB::table('ak_products')
      ->select('ak_products.slug')
      ->where('is_active', 1)
      ->get();

    $products_array = $products->all();

    $products_links = array_map(function($item) {
      return '/' . $item->slug;
    }, $products_array);

    $products_links_ru = array_map(function($item) {
      return '/ru/' . $item->slug;
    }, $products_array);
    

    return [
      ...$products_links,
      ...$products_links_ru
    ];
  }
  
  /**
   * getCategories
   *
   * @param  mixed $request
   * @return void
   */
  public function getCategories(Request $request) {
    
    $categories = \DB::table('ak_product_categories')
      ->select('ak_product_categories.slug')
      ->where('is_active', 1)
      ->get();

    $categories_array = $categories->all();

    $categories_links = array_map(function($item) {
      return '/' . $item->slug;
    }, $categories_array);

    $categories_links_ru = array_map(function($item) {
      return '/ru/' . $item->slug;
    }, $categories_array);

    return [
      ...$categories_links,
      ...$categories_links_ru
    ];
  }

  /**
   * getRegions
   *
   * @param  mixed $request
   * @return void
   */
  public function getRegions(Request $request) {
    
    $regions = \DB::table('regions')
      ->select('regions.slug')
      ->where('is_active', 1)
      ->get();

    $regions_array = $regions->all();

    $regions_links = array_map(function($item) {
      return '/' . $item->slug;
    }, $regions_array);

    $regions_links_ru = array_map(function($item) {
      return '/ru/' . $item->slug;
    }, $regions_array);

    return [
      ...$regions_links,
      ...$regions_links_ru
    ];
  }
}