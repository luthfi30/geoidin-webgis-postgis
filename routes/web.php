<?php

use App\Http\Controllers\MapController;
use Illuminate\Support\Facades\Route;

// 1. Halaman Peta sekarang menjadi Halaman Utama (Homepage)
Route::view('/', 'map-view')->name('home');

// 2. Route API (Kita tangani keamanan di dalam Controller, bukan di Middleware)
Route::prefix('api')->group(function () {
    Route::get('/layers', [MapController::class, 'getLayers']);
    Route::get('/layers/{id}/features', [MapController::class, 'getFeatureData']);
});

// 3. Route Laravel yang membutuhkan login (Logout, dll)
Route::middleware(['auth'])->group(function () {
    // Anda bisa menaruh route lain yang khusus admin/staff di sini
});