<?php

use Backpack\Schedule\Http\Controllers\Admin\ScheduledPublicationCrudController;

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
    'namespace' => 'Backpack\Schedule\Http\Controllers\Admin',
], function () {
    Route::crud('scheduled-publication', 'ScheduledPublicationCrudController');
    
    // Дополнительные маршруты для действий
    Route::post('scheduled-publication/{id}/cancel', [ScheduledPublicationCrudController::class, 'cancel'])
        ->name('scheduled-publication.cancel');
    
    Route::post('scheduled-publication/{id}/publish-now', [ScheduledPublicationCrudController::class, 'publishNow'])
        ->name('scheduled-publication.publish-now');
    
    Route::post('scheduled-publication/bulk-cancel', [ScheduledPublicationCrudController::class, 'bulkCancel'])
        ->name('scheduled-publication.bulk-cancel');
});
