<?php

use Illuminate\Support\Facades\Route;
use ParabellumKoval\Webhooks\Http\Controllers\Admin\WebhookUnitController;

Route::group([
    'namespace'  => 'ParabellumKoval\\Webhooks\\Http\\Controllers\\Admin',
    'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
], function () {
    Route::prefix('frontend-cache-refresh')->group(function () {
        Route::post('/', [WebhookUnitController::class, 'refresh'])
            ->name('admin.frontend-cache-refresh.refresh');

        Route::get('/status', [WebhookUnitController::class, 'status'])
            ->name('admin.frontend-cache-refresh.status');

        Route::get('/status/{unit}', [WebhookUnitController::class, 'unitStatus'])
            ->where('unit', '[A-Za-z0-9_-]+')
            ->name('admin.frontend-cache-refresh.unit-status');

        Route::delete('/status', [WebhookUnitController::class, 'clearStatusCache'])
            ->name('admin.frontend-cache-refresh.clear-status');
    });
});
