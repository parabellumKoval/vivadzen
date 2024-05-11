<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\LiqpayController;

Route::prefix('liqpay')->controller(LiqpayController::class)->group(function () {
  Route::post('/form', 'generateForm');
  Route::post('/callback', 'callback');
  Route::post('/results', 'results');
  Route::get('/results', 'results');
});
