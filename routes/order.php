<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('p/{project:slug}/control-order')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::get('create', [OrderController::class, 'create']);
    Route::post('/', [OrderController::class, 'store']);

    Route::post('rfi', [OrderController::class, 'storeRfi']);
    Route::put('rfi/{rfi}', [OrderController::class, 'updateRfi']);
    Route::delete('rfi/{rfi}', [OrderController::class, 'destroyRfi']);

    Route::get('{order}/edit', [OrderController::class, 'edit']);
    Route::put('{order}', [OrderController::class, 'update']);
    Route::delete('{order}', [OrderController::class, 'destroy']);
});