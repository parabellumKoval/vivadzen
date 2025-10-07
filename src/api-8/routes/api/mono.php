<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\MonoController;

Route::prefix('mono')->controller(MonoController::class)->group(function () {
  // route for monobank
  Route::any('/callback', 'callback');

  // Route for frontend
  Route::post('/create', 'create');
});
