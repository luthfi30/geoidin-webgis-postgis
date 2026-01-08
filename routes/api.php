<?php

use App\Http\Controllers\Api\GisController;
use Illuminate\Support\Facades\Route;

// Endpoint ini akan bisa diakses di: localhost:8000/api/gis-data
Route::get('/gis-data', [GisController::class, 'getFeatures']);
