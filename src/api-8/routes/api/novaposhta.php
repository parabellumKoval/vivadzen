<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\NovaposhtaController;

Route::prefix('np')->controller(NovaposhtaController::class)->group(function () {
  Route::get('/counterpartyList', 'counterpartyList');
  Route::get('/addressList', 'addressList');
  Route::get('/streetFind', 'streetFind');
  Route::get('/counterpartyContacts', 'counterpartyContacts');
  Route::get('/cityFind', 'cityFind');
  Route::get('/settlementFind', 'settlementFind');
  Route::get('/warehouseFind', 'warehouseFind');
  Route::get('/contactsList', 'contactsList');
});
