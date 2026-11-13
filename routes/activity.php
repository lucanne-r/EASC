<?php

use App\Http\Controllers\ActivityController;
use Illuminate\Support\Facades\Route;

Route::prefix('p/{slug}/control-activity')->name('activity.')->group(function () {
    Route::get('/', [ActivityController::class, 'index'])->name('index');
    Route::get('/export', [ActivityController::class, 'export'])->name('export');
    Route::get('/next-id', [ActivityController::class, 'nextId'])->name('next-id');
    Route::post('/', [ActivityController::class, 'store'])->name('store');
    Route::put('/{id}', [ActivityController::class, 'update'])->name('update');
    Route::post('/{id}/close', [ActivityController::class, 'close'])->name('close');
    Route::post('/{id}/delete', [ActivityController::class, 'destroy'])->name('delete');
    Route::post('/{id}/attachments', [ActivityController::class, 'upload'])->name('upload');
    Route::get('/attachments/{att}', [ActivityController::class, 'download'])->name('download');
    Route::delete('/attachments/{att}', [ActivityController::class, 'deleteAttachment'])->name('attachment.delete');
});