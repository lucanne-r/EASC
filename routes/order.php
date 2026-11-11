<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('p/{project:slug}/control-order')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    // rute tambah/edit/hapus Control Order ditambahkan di sini
});