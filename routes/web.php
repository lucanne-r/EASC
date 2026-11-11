<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $projects = [
        ['name' => 'Kakotora Line 1', 'desc' => 'Database aktivitas sendiri'],
        ['name' => 'Kakotora Line 4', 'desc' => 'Database aktivitas sendiri'],
        ['name' => 'Relayout Line 2', 'desc' => 'Database aktivitas sendiri'],
    ];

    return view('home', compact('projects'));
});