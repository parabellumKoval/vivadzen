<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\FrontendCacheRefreshController;

/*
|--------------------------------------------------------------------------
| Frontend Cache Refresh Routes
|--------------------------------------------------------------------------
|
| This file contains the routes for the frontend cache refresh functionality
| in the admin panel.
|
*/

Route::group([
    'namespace'  => 'App\Http\Controllers\Admin',
    'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
], function () {
    
    // Frontend Cache Refresh routes
    Route::prefix('frontend-cache-refresh')->group(function () {
        // Trigger cache refresh for a specific unit
        Route::post('/', [FrontendCacheRefreshController::class, 'refresh'])
             ->name('admin.frontend-cache-refresh.refresh');
        
        // Get status of all cache refresh units
        Route::get('/status', [FrontendCacheRefreshController::class, 'status'])
             ->name('admin.frontend-cache-refresh.status');
        
        // Get status of a specific cache refresh unit
        Route::get('/status/{unitUrl}', [FrontendCacheRefreshController::class, 'unitStatus'])
             ->where('unitUrl', '.*')
             ->name('admin.frontend-cache-refresh.unit-status');
        
        // Clear status cache for all units
        Route::delete('/status', [FrontendCacheRefreshController::class, 'clearStatusCache'])
             ->name('admin.frontend-cache-refresh.clear-status');
    });
    
});