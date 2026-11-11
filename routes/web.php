<?php

use App\Http\Controllers\ProjectController;
use App\Models\Project;
use Illuminate\Support\Facades\Route;

// ===== KERANGKA (jangan diubah tanpa kesepakatan) =====
Route::get('/', [ProjectController::class, 'index']);
Route::post('/projects', [ProjectController::class, 'store']);
Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
Route::get('/p/{project:slug}', fn (Project $project) => redirect("/p/{$project->slug}/parameter"));

// ===== MENU: tiap menu punya file rute sendiri =====
require __DIR__ . '/parameter.php';
require __DIR__ . '/activity.php';
require __DIR__ . '/order.php';