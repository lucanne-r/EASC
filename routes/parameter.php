<?php

use App\Http\Controllers\ParameterController;
use Illuminate\Support\Facades\Route;

Route::prefix('p/{project:slug}/parameter')->group(function () {
    Route::get('/', [ParameterController::class, 'index']);

    // Standar Timbangan
    Route::get('standar/create', [ParameterController::class, 'stdCreate']);
    Route::post('standar', [ParameterController::class, 'stdStore']);
    Route::get('standar/{standard}/edit', [ParameterController::class, 'stdEdit']);
    Route::put('standar/{standard}', [ParameterController::class, 'stdUpdate']);
    Route::delete('standar/{standard}', [ParameterController::class, 'stdDestroy']);
    Route::patch('standar/{standard}/inline', [ParameterController::class, 'stdInline']);
    // DC Code
    Route::get('dc/create', [ParameterController::class, 'dcCreate']);
    Route::post('dc', [ParameterController::class, 'dcStore']);
    Route::get('dc/{dc}/edit', [ParameterController::class, 'dcEdit']);
    Route::put('dc/{dc}', [ParameterController::class, 'dcUpdate']);
    Route::delete('dc/{dc}', [ParameterController::class, 'dcDestroy']);
    Route::patch('dc/{dc}/inline', [ParameterController::class, 'dcInline']);
    // File barcode per DC Code
    Route::post('dc/{dc}/barcode', [ParameterController::class, 'barcodeUpload']);
    Route::get('dc/{dc}/barcode', [ParameterController::class, 'barcodeShow']);
    Route::delete('dc/{dc}/barcode', [ParameterController::class, 'barcodeDelete']);
});