<?php

use hexa_package_gnews\Http\Controllers\GNewsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| GNews Package Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth', 'locked', 'system_lock', 'two_factor', 'role'])->group(function () {
    // Raw dev view
    Route::get('/raw-gnews', [GNewsController::class, 'raw'])->name('gnews.index');

    // Settings
    Route::get('/settings/gnews', [GNewsController::class, 'settings'])->name('settings.gnews');
    Route::post('/settings/gnews/save', [GNewsController::class, 'saveSettings'])->name('settings.gnews.save');
    Route::post('/settings/gnews/test', [GNewsController::class, 'testApiKey'])->name('settings.gnews.test');

    // API endpoints (shared by raw and production views)
    Route::post('/gnews/search', [GNewsController::class, 'searchArticles'])->name('gnews.search');
});
