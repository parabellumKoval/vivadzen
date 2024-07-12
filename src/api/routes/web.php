<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MigrateDbController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\TranslateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/', '/admin', 301);

Route::get('/import_attrs', [ImportController::class, 'import']);

Route::get('/html', function(){
  Artisan::call('db-copy:normalize-product-content');
});

Route::get('/migr', [MigrateDbController::class, 'all']);

Route::get('/mailable', function () {
  // $feedback = Backpack\Feedback\app\Models\Feedback::find(40);
  // return new App\Mail\Buy1ClickCreatedAdmin($feedback);

  // $order = \Backpack\Store\app\Models\Order::find(28);
  // return new App\Mail\OrderCreatedAdmin($order);

  $order = \Backpack\Store\app\Models\Order::find(28);
  return new App\Mail\OrderCreated($order);

});

Route::prefix('auth')->group(function() {
	Route::middleware('web')->any('/{provider}', 'App\Http\Controllers\Auth\OAuthController@redirect')->where('provider', 'google|facebook');
	Route::middleware('web')->get('/{provider}/callback', 'App\Http\Controllers\Auth\OAuthController@callback')->where('provider', 'google|facebook');
	Route::middleware('web')->get('/loginByToken', 'App\Http\Controllers\Auth\OAuthController@loginByToken');
});