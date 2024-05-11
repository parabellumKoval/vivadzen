<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MigrateDbController;

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

Route::get('/html', function(){
  Artisan::call('db-copy:normalize-product-content');
});

Route::get('/migr', [MigrateDbController::class, 'all']);

Route::get('/mailable', function () {
  // $feedback = Backpack\Feedback\app\Models\Feedback::find(40);
  // return new App\Mail\Buy1ClickCreatedAdmin($feedback);

  // $order = App\Models\Override\Order::find(216);
  // return new App\Mail\OrderCreatedAdmin($order);

  // $order = App\Models\Override\Order::find(215);
  // return new App\Mail\OrderCreated($order);

  // $user = App\Models\Override\Profile::find(24);
  // return new App\Mail\UserRegistered($user);

  // $user = App\Models\Override\Profile::find(24);
  // return new App\Mail\ReferralRegistered($user);

  // $transaction = Backpack\Transactions\app\Models\Transaction::find(1132);
  // return new App\Mail\WithdrawalCompletedAdmin($transaction);

  // $transaction = Backpack\Transactions\app\Models\Transaction::find(963);
  // return new App\Mail\WithdrawalCompleted($transaction);

  // $transaction = Backpack\Transactions\app\Models\Transaction::find(963);
  // return new App\Mail\ReferralBonus($transaction);

  // $transaction = Backpack\Transactions\app\Models\Transaction::find(963);
  // return new App\Mail\CashbackBonus($transaction);

  // $transaction = Backpack\Transactions\app\Models\Transaction::find(963);
  // return new App\Mail\ReviewBonus($transaction);
});

Route::prefix('auth')->group(function() {
	Route::middleware('web')->any('/{provider}', 'App\Http\Controllers\Auth\OAuthController@redirect')->where('provider', 'google|facebook');
	Route::middleware('web')->get('/{provider}/callback', 'App\Http\Controllers\Auth\OAuthController@callback')->where('provider', 'google|facebook');
	Route::middleware('web')->get('/loginByToken', 'App\Http\Controllers\Auth\OAuthController@loginByToken');
});