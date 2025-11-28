<?php

use Illuminate\Support\Facades\Route;
use ParabellumKoval\Dumper\Http\Controllers\Admin\DumperController;

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
], function () {
    Route::get('dumper', [DumperController::class, 'index'])->name('backpack.dumper.index');
    Route::post('dumper/manual', [DumperController::class, 'store'])->name('backpack.dumper.manual');
    Route::post('dumper/restore', [DumperController::class, 'restore'])->name('backpack.dumper.restore');
    Route::get('dumper/download/{reference}', [DumperController::class, 'download'])->name('backpack.dumper.download');
    Route::post('dumper/auto/{case}/run', [DumperController::class, 'runAutoCase'])->name('backpack.dumper.auto.run');
});
