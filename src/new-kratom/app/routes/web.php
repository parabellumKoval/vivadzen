<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReviewController;
use App\Support\Catalog;
use App\Support\Locale;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Localized routes
|--------------------------------------------------------------------------
| Czech (cs) is the default locale and has no URL prefix.
| Other locales: /en/..., /ru/..., /uk/...
*/

$nonDefault = collect(Locale::SUPPORTED)
    ->reject(fn ($code) => $code === Locale::DEFAULT)
    ->implode('|');

$register = function () {
    Route::view('/', 'pages.home')->name('home');
    Route::view('/styleguide', 'pages.styleguide')->name('styleguide');

    // Catalog
    Route::get('/kratom', function () {
        return view('pages.catalog.index', [
            'taxonomyType' => null,
            'taxonomyKey'  => null,
        ]);
    })->name('catalog');

    Route::get('/kratom/{slug}', function (string $slug) {
        if ($product = Catalog::find($slug)) {
            return view('pages.product.show', ['product' => $product]);
        }
        if ($taxonomy = Catalog::resolveTaxonomy($slug)) {
            return view('pages.catalog.taxonomy', [
                'taxonomyType' => $taxonomy['type'],
                'taxonomyKey'  => $taxonomy['key'],
                'taxonomy'     => $taxonomy['data'],
            ]);
        }
        abort(404);
    })->where('slug', '[a-z0-9\-]+')->name('catalog.entry');

    // Reviews & Questions (per-product)
    Route::get('/api/product/{slug}/reviews', [ReviewController::class, 'listReviews'])
        ->where('slug', '[a-z0-9\-]+')->name('product.reviews.list');
    Route::post('/api/product/{slug}/reviews', [ReviewController::class, 'storeReview'])
        ->where('slug', '[a-z0-9\-]+')->name('product.reviews.store');
    Route::get('/api/product/{slug}/questions', [ReviewController::class, 'listQuestions'])
        ->where('slug', '[a-z0-9\-]+')->name('product.questions.list');
    Route::post('/api/product/{slug}/questions', [ReviewController::class, 'storeQuestion'])
        ->where('slug', '[a-z0-9\-]+')->name('product.questions.store');
    Route::post('/api/review/{review}/helpful', [ReviewController::class, 'helpfulReview'])
        ->name('product.review.helpful');
    Route::post('/api/question/{question}/helpful', [ReviewController::class, 'helpfulQuestion'])
        ->name('product.question.helpful');

    // Cart
    Route::get('/kosik', [CartController::class, 'index'])->name('cart.index');
    Route::post('/kosik/pridat', [CartController::class, 'add'])->name('cart.add');
    Route::post('/kosik/aktualizovat', [CartController::class, 'update'])->name('cart.update');
    Route::post('/kosik/odebrat', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/kosik/data', [CartController::class, 'data'])->name('cart.data');
    Route::post('/kosik/promo', [CartController::class, 'promo'])->name('cart.promo');

    // Checkout
    Route::get('/pokladna', [CheckoutController::class, 'delivery'])->name('checkout.delivery');
    Route::post('/pokladna/doruceni', [CheckoutController::class, 'saveDelivery'])->name('checkout.delivery.save');
    Route::get('/pokladna/platba', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/pokladna/platba', [CheckoutController::class, 'savePayment'])->name('checkout.payment.save');
    Route::get('/pokladna/potvrzeni', [CheckoutController::class, 'review'])->name('checkout.review');
    Route::post('/pokladna/dokoncit', [CheckoutController::class, 'submit'])->name('checkout.submit');

    // Order status
    Route::get('/objednavka/uspech', [CheckoutController::class, 'success'])->name('order.success');
    Route::get('/objednavka/chyba', [CheckoutController::class, 'error'])->name('order.error');
    Route::get('/api/order/{publicId}/status', [CheckoutController::class, 'status'])
        ->where('publicId', 'VZ-[0-9]{4}-[0-9]{4}')
        ->name('order.status');

    // Static pages
    Route::get('/doruceni', [PageController::class, 'delivery'])->name('page.delivery');
    Route::get('/laboratorni-testy', [PageController::class, 'lab'])->name('page.lab');
    Route::get('/licence', [PageController::class, 'licence'])->name('page.licence');
    Route::get('/prodejny', [PageController::class, 'stores'])->name('page.stores');
    Route::get('/reklamace', [PageController::class, 'returns'])->name('page.returns');
    Route::get('/kontakt', [PageController::class, 'contact'])->name('page.contact');
    Route::get('/podpora', [PageController::class, 'support'])->name('page.support');
    Route::get('/o-nas', [PageController::class, 'about'])->name('page.about');
    Route::get('/obchodni-podminky', [PageController::class, 'terms'])->name('page.terms');
    Route::get('/ochrana-osobnich-udaju', [PageController::class, 'privacy'])->name('page.privacy');
    Route::get('/cookies', [PageController::class, 'cookies'])->name('page.cookies');
    Route::get('/predplatne', [PageController::class, 'subscription'])->name('page.subscription');
    Route::get('/pruvodce', [PageController::class, 'guide'])->name('page.guide');
};

// Default (cs) — no prefix
$register();

// Localized routes (en, ru, uk)
Route::prefix('{locale}')
    ->where(['locale' => $nonDefault])
    ->group($register);
