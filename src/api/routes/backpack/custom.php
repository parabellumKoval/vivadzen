<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
  Route::crud('prompt', 'PromptCrudController');
  Route::crud('region', 'RegionCrudController');
  Route::crud('feed', 'FeedCrudController');
  Route::crud('payment', 'PaymentCrudController');

  // 
  Route::crud('translation-history', 'TranslationHistoryCrudController');
  Route::post('translation-history/settings', 'TranslationHistoryCrudController@saveSettings');

  //
  Route::crud('ai-generation-history', 'AiGenerationHistoryCrudController');
  Route::post('ai-generation-history/settings', 'AiGenerationHistoryCrudController@saveAiGenerationSettings');

  //
  Route::crud('image-generation-history', 'ImageGenerationHistoryCrudController');
  Route::post('image-generation-history/settings', 'ImageGenerationHistoryCrudController@saveImageGenerationSettings');
}); // this should be the absolute last line of this file
