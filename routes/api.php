<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContainerController;
use App\Http\Controllers\Api\SmallBaleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Container Routes
    Route::apiResource('containers', ContainerController::class);
    Route::get('/opened-bales', [ContainerController::class, 'getOpenedBales']);
    Route::post('/opened-bales', [ContainerController::class, 'storeOpenedBales']);

    // Small Bale Routes
    Route::apiResource('small-bales', SmallBaleController::class);
    Route::post('/productions/batch', [SmallBaleController::class, 'storeProductionBatch']);
});
