<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\TaxonomyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API (Sanctum personal access tokens)
|--------------------------------------------------------------------------
| Префикс /admin-api. Используется отдельным Nuxt-фронтом (порт 3002).
| Гость: только /login. Остальное — под guard `sanctum` + admin-абилка.
*/

Route::prefix('admin-api')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Products
        Route::get('/products', [ProductController::class, 'index']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        // Taxonomies (color/strain/form/region)
        Route::get('/taxonomies', [TaxonomyController::class, 'index']);
        Route::post('/taxonomies', [TaxonomyController::class, 'store']);
        Route::put('/taxonomies/{taxonomy}', [TaxonomyController::class, 'update']);
        Route::delete('/taxonomies/{taxonomy}', [TaxonomyController::class, 'destroy']);

        // Orders
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order:public_id}', [OrderController::class, 'show']);
        Route::post('/orders/{order:public_id}/status', [OrderController::class, 'updateStatus']);

        // Media
        Route::get('/media', [MediaController::class, 'index']);
        Route::post('/media', [MediaController::class, 'store']);
        Route::delete('/media/{media}', [MediaController::class, 'destroy']);

        // Reviews
        Route::get('/reviews', [ReviewController::class, 'index']);
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::get('/reviews/{review}', [ReviewController::class, 'show']);
        Route::put('/reviews/{review}', [ReviewController::class, 'update']);
        Route::post('/reviews/{review}', [ReviewController::class, 'update']); // for multipart uploads
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);
        Route::post('/reviews/{review}/approve', [ReviewController::class, 'approve']);
        Route::post('/reviews/{review}/reject', [ReviewController::class, 'reject']);
        Route::delete('/reviews/{review}/photos/{image}', [ReviewController::class, 'deletePhoto']);

        // Questions
        Route::get('/questions', [QuestionController::class, 'index']);
        Route::post('/questions', [QuestionController::class, 'store']);
        Route::get('/questions/{question}', [QuestionController::class, 'show']);
        Route::put('/questions/{question}', [QuestionController::class, 'update']);
        Route::delete('/questions/{question}', [QuestionController::class, 'destroy']);
        Route::post('/questions/{question}/approve', [QuestionController::class, 'approve']);
        Route::post('/questions/{question}/reject', [QuestionController::class, 'reject']);

        // Cache control
        Route::post('/cache/warm', function () {
            app(\App\Services\CacheWarmer::class)->warmAll();
            return response()->json(['ok' => true]);
        });
    });
});
