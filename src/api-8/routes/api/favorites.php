<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\FavoritesController;

Route::prefix('favorites')->controller(FavoritesController::class)->group(function () {
  Route::get('/', 'index');
  
  Route::get('/ids', 'ids');

  Route::get('/sync', 'sync');
});
