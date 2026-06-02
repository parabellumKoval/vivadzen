<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\AddressController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PruvodceController;
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

    // Reviews (all products)
    Route::get('/recenze', [ReviewController::class, 'page'])->name('reviews.page');
    Route::get('/api/reviews', [ReviewController::class, 'listAllReviews'])->name('reviews.all');
    Route::post('/api/reviews', [ReviewController::class, 'storeGlobalReview'])->name('reviews.store');

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

    // Forum
    Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
    Route::get('/forum/nove-tema', [ForumController::class, 'create'])->name('forum.create');
    Route::post('/forum/nove-tema', [ForumController::class, 'store'])
        ->middleware('auth')->name('forum.store');
    Route::get('/forum/tema/{slug}', [ForumController::class, 'topic'])
        ->where('slug', '[a-z0-9\-]+')->name('forum.topic');
    Route::put('/forum/tema/{topic}', [ForumController::class, 'updateTopic'])
        ->middleware('auth')->name('forum.topic.update');
    Route::post('/forum/tema/{topic}/odpovedi', [ForumController::class, 'storePost'])
        ->middleware('auth')->name('forum.post.store');
    Route::put('/forum/prispevek/{post}', [ForumController::class, 'updatePost'])
        ->middleware('auth')->name('forum.post.update');
    Route::post('/forum/prispevek/{post}/hlas', [ForumController::class, 'votePost'])
        ->middleware('auth')->name('forum.post.vote');
    Route::post('/forum/prispevek/{post}/reakce', [ForumController::class, 'toggleReaction'])
        ->middleware('auth')->name('forum.post.reaction');
    Route::get('/forum/uzivatel/{slug}', [ForumController::class, 'user'])
        ->where('slug', '[a-z0-9\-]+')->name('forum.user');

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

    // Customer account (auth required)
    Route::middleware('auth')->prefix('ucet')->name('account.')->group(function () {
        Route::get('/', [AccountController::class, 'profile'])->name('profile');
        Route::post('/profil', [AccountController::class, 'updateProfile'])->name('profile.update');
        Route::post('/avatar', [AccountController::class, 'updateAvatar'])->name('avatar.update');
        Route::delete('/avatar', [AccountController::class, 'deleteAvatar'])->name('avatar.delete');

        Route::get('/zabezpeceni', [AccountController::class, 'security'])->name('security');
        Route::post('/heslo', [AccountController::class, 'updatePassword'])->name('password.update');
        Route::post('/email', [AccountController::class, 'updateEmail'])->name('email.update');

        Route::get('/adresy', [AccountController::class, 'addresses'])->name('addresses');
        Route::post('/adresy', [AddressController::class, 'store'])->name('addresses.store');
        Route::put('/adresy/{address}', [AddressController::class, 'update'])->name('addresses.update');
        Route::delete('/adresy/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
        Route::post('/adresy/{address}/vychozi', [AddressController::class, 'setDefault'])->name('addresses.default');

        Route::get('/mesta', [\App\Http\Controllers\Account\CitySearchController::class, 'index'])->name('cities.search');

        Route::get('/objednavky', [AccountController::class, 'orders'])->name('orders');
        Route::get('/objednavky/{order}', [AccountController::class, 'orderDetail'])
            ->where('order', 'VZ-[0-9]{4}-[0-9]{4}')->name('orders.detail');

        Route::get('/recenze', [AccountController::class, 'reviews'])->name('reviews');
        Route::delete('/recenze/{review}', [AccountController::class, 'destroyReview'])->name('reviews.destroy');
        Route::get('/forum-temata', [AccountController::class, 'forumTopics'])->name('forum.topics');
        Route::get('/forum-prispevky', [AccountController::class, 'forumPosts'])->name('forum.posts');
        Route::delete('/forum-tema/{topic}', [AccountController::class, 'destroyTopic'])->name('forum.topics.destroy');
        Route::put('/forum-tema/{topic}', [AccountController::class, 'updateTopic'])->name('forum.topics.update');
        Route::delete('/forum-prispevek/{post}', [AccountController::class, 'destroyPost'])->name('forum.posts.destroy');
        Route::put('/forum-prispevek/{post}', [AccountController::class, 'updatePost'])->name('forum.posts.update');
    });

    // Static pages
    Route::get('/doruceni', [PageController::class, 'delivery'])->name('page.delivery');
    Route::get('/laboratorni-testy', [PageController::class, 'lab'])->name('page.lab');
    Route::get('/sarze/{batch}', [PageController::class, 'labBatch'])
        ->where('batch', '[A-Za-z0-9\-]+')
        ->name('page.lab.batch');
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
    Route::get('/overeni-veku', [PageController::class, 'ageVerification'])->name('page.age-verification');
    // Pruvodce (wiki)
    Route::get('/pruvodce', [PruvodceController::class, 'index'])->name('pruvodce.index');
    Route::get('/pruvodce/{category}', [PruvodceController::class, 'category'])
        ->where('category', '[a-z0-9\-]+')
        ->name('pruvodce.category');
    Route::get('/pruvodce/{category}/{slug}', [PruvodceController::class, 'article'])
        ->where(['category' => '[a-z0-9\-]+', 'slug' => '[a-z0-9\-]+'])
        ->name('pruvodce.article');
};

// Default (cs) — no prefix
$register();

// Localized routes (en, ru, uk)
Route::prefix('{locale}')
    ->where(['locale' => $nonDefault])
    ->group($register);

/*
|--------------------------------------------------------------------------
| Auth (non-localized fixed URLs)
|--------------------------------------------------------------------------
| Login/register/logout are AJAX endpoints used by the auth modal.
| Social callbacks and signed links must be stable URLs, so they live
| outside the {locale} prefix group.
*/

Route::post('/auth/login', [LoginController::class, 'store'])
    ->middleware('throttle:10,1')->name('auth.login');
Route::post('/auth/register', [RegisterController::class, 'store'])
    ->middleware('throttle:10,1')->name('auth.register');
Route::post('/auth/logout', [LoginController::class, 'destroy'])->name('auth.logout');

Route::get('/auth/{provider}/redirect', [SocialController::class, 'redirect'])
    ->whereIn('provider', ['google', 'facebook'])->name('auth.social.redirect');
Route::get('/auth/{provider}/callback', [SocialController::class, 'callback'])
    ->whereIn('provider', ['google', 'facebook'])->name('auth.social.callback');

// Password reset
Route::post('/auth/forgot-password', [PasswordResetController::class, 'sendResetLink'])
    ->middleware('throttle:6,1')->name('password.email');
Route::get('/obnoveni-hesla/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/obnoveni-hesla', [PasswordResetController::class, 'reset'])->name('password.update');

// Email verification
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('signed')->name('verification.verify');
Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
    ->middleware(['auth', 'throttle:6,1'])->name('verification.send');
