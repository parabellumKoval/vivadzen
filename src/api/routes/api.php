<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SitemapController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('sitemap')->controller(SitemapController::class)->group(function () {
  Route::get('/categories', 'getCategories')->middleware('api');
  Route::get('/products', 'getProducts')->middleware('api');
  Route::get('/brands', 'getBrands')->middleware('api');
  Route::get('/articles', 'getArticles')->middleware('api');
});

Route::get('/product_or_category/{slug}', [CategoryController::class, 'productOrCategory'])->middleware('api');
Route::get('/catalog_data', [CategoryController::class, 'catalogData'])->middleware('api');

Route::get('/djini-category/slugs', [CategoryController::class, 'getSlugs'])->middleware('api');

Route::prefix('search')->controller(SearchController::class)->group(function () {
  Route::get('', 'index')->middleware('api');
  Route::get('/livesearch', 'livesearch')->middleware('api');
});
