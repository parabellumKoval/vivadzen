<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryMethodController;
use App\Http\Controllers\Admin\EditorMediaController;
use App\Http\Controllers\Admin\ForumCategoryController;
use App\Http\Controllers\Admin\ForumPostController;
use App\Http\Controllers\Admin\ForumTopicController;
use App\Http\Controllers\Admin\LabBatchController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\TaxonomyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WikiArticleController;
use App\Http\Controllers\Admin\WikiCategoryController;
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

        // Product gallery (multi-image upload + reorder + alt/title)
        Route::get('/products/{product}/images', [ProductImageController::class, 'index']);
        Route::post('/products/{product}/images', [ProductImageController::class, 'store']);
        Route::put('/products/{product}/images/reorder', [ProductImageController::class, 'reorder']);
        Route::put('/products/{product}/images/{image}', [ProductImageController::class, 'update']);
        Route::delete('/products/{product}/images/{image}', [ProductImageController::class, 'destroy']);

        // Lab batches (COA / Šarže)
        Route::get('/lab-batches', [LabBatchController::class, 'index']);
        Route::post('/lab-batches', [LabBatchController::class, 'store']);
        Route::get('/lab-batches/{labBatch}', [LabBatchController::class, 'show']);
        Route::put('/lab-batches/{labBatch}', [LabBatchController::class, 'update']);
        Route::delete('/lab-batches/{labBatch}', [LabBatchController::class, 'destroy']);
        Route::post('/lab-batches/{labBatch}/files', [LabBatchController::class, 'storeFile']);
        Route::put('/lab-batches/{labBatch}/files/reorder', [LabBatchController::class, 'reorderFiles']);
        Route::put('/lab-batches/{labBatch}/files/{file}', [LabBatchController::class, 'updateFile']);
        Route::delete('/lab-batches/{labBatch}/files/{file}', [LabBatchController::class, 'destroyFile']);

        // Taxonomies (color/strain/form/region)
        Route::get('/taxonomies', [TaxonomyController::class, 'index']);
        Route::post('/taxonomies', [TaxonomyController::class, 'store']);
        Route::put('/taxonomies/{taxonomy}', [TaxonomyController::class, 'update']);
        Route::delete('/taxonomies/{taxonomy}', [TaxonomyController::class, 'destroy']);

        // Orders
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order:public_id}', [OrderController::class, 'show']);
        Route::post('/orders/{order:public_id}/status', [OrderController::class, 'updateStatus']);

        // Media (общий медиа-кабинет)
        Route::get('/media', [MediaController::class, 'index']);
        Route::post('/media', [MediaController::class, 'store']);
        Route::delete('/media/{media}', [MediaController::class, 'destroy']);

        // Загрузка изображений из WYSIWYG-редактора (TipTap)
        Route::post('/editor/image', [EditorMediaController::class, 'image']);

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

        // Forum moderation
        Route::get('/forum/categories', [ForumCategoryController::class, 'index']);
        Route::post('/forum/categories', [ForumCategoryController::class, 'store']);
        Route::put('/forum/categories/{category}', [ForumCategoryController::class, 'update']);
        Route::delete('/forum/categories/{category}', [ForumCategoryController::class, 'destroy']);

        Route::get('/forum/topics', [ForumTopicController::class, 'index']);
        Route::post('/forum/topics', [ForumTopicController::class, 'store']);
        Route::get('/forum/topics/{topic}', [ForumTopicController::class, 'show']);
        Route::put('/forum/topics/{topic}', [ForumTopicController::class, 'update']);
        Route::delete('/forum/topics/{topic}', [ForumTopicController::class, 'destroy']);
        Route::post('/forum/topics/{topic}/approve', [ForumTopicController::class, 'approve']);
        Route::post('/forum/topics/{topic}/reject', [ForumTopicController::class, 'reject']);

        Route::get('/forum/posts', [ForumPostController::class, 'index']);
        Route::post('/forum/posts', [ForumPostController::class, 'store']);
        Route::get('/forum/posts/{post}', [ForumPostController::class, 'show']);
        Route::put('/forum/posts/{post}', [ForumPostController::class, 'update']);
        Route::delete('/forum/posts/{post}', [ForumPostController::class, 'destroy']);
        Route::post('/forum/posts/{post}/approve', [ForumPostController::class, 'approve']);
        Route::post('/forum/posts/{post}/reject', [ForumPostController::class, 'reject']);

        // Pruvodce (wiki)
        Route::get('/pruvodce/categories', [WikiCategoryController::class, 'index']);
        Route::post('/pruvodce/categories', [WikiCategoryController::class, 'store']);
        Route::get('/pruvodce/categories/{category}', [WikiCategoryController::class, 'show']);
        Route::put('/pruvodce/categories/{category}', [WikiCategoryController::class, 'update']);
        Route::delete('/pruvodce/categories/{category}', [WikiCategoryController::class, 'destroy']);

        Route::get('/pruvodce/articles', [WikiArticleController::class, 'index']);
        Route::post('/pruvodce/articles', [WikiArticleController::class, 'store']);
        Route::get('/pruvodce/articles/{article}', [WikiArticleController::class, 'show']);
        Route::put('/pruvodce/articles/{article}', [WikiArticleController::class, 'update']);
        Route::delete('/pruvodce/articles/{article}', [WikiArticleController::class, 'destroy']);
        Route::post('/pruvodce/articles/{article}/publish', [WikiArticleController::class, 'publish']);
        Route::post('/pruvodce/articles/{article}/unpublish', [WikiArticleController::class, 'unpublish']);
        Route::post('/pruvodce/articles/{article}/cover', [WikiArticleController::class, 'uploadCover']);

        // Customers
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::post('/users/{user}/block', [UserController::class, 'block']);
        Route::post('/users/{user}/unblock', [UserController::class, 'unblock']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);

        // Delivery methods
        Route::get('/delivery-methods', [DeliveryMethodController::class, 'index']);
        Route::post('/delivery-methods', [DeliveryMethodController::class, 'store']);
        Route::put('/delivery-methods/reorder', [DeliveryMethodController::class, 'reorder']);
        Route::get('/delivery-methods/{deliveryMethod}', [DeliveryMethodController::class, 'show']);
        Route::put('/delivery-methods/{deliveryMethod}', [DeliveryMethodController::class, 'update']);
        Route::delete('/delivery-methods/{deliveryMethod}', [DeliveryMethodController::class, 'destroy']);

        // Payment methods
        Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
        Route::post('/payment-methods', [PaymentMethodController::class, 'store']);
        Route::put('/payment-methods/reorder', [PaymentMethodController::class, 'reorder']);
        Route::get('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'show']);
        Route::put('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'update']);
        Route::delete('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'destroy']);

        // Cache control
        Route::post('/cache/warm', function () {
            app(\App\Services\CacheWarmer::class)->warmAll();
            return response()->json(['ok' => true]);
        });
    });
});
