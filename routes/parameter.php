<?php

use App\Http\Controllers\ParameterController;
use Illuminate\Support\Facades\Route;

Route::prefix('p/{project:slug}/parameter')->group(function () {
    Route::get('/', [ParameterController::class, 'index']);
    // rute tambah/edit/hapus Parameter ditambahkan di sini
});