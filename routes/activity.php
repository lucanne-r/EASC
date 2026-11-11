<?php

use App\Http\Controllers\ActivityController;
use Illuminate\Support\Facades\Route;

Route::prefix('p/{project:slug}/control-activity')->group(function () {
    Route::get('/', [ActivityController::class, 'index']);
    // rute tambah/edit/hapus Control Activity ditambahkan di sini
});