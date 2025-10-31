<?php


use App\Http\Controllers\OwnerProductController;
use App\Http\Controllers\Supervisor\SupervisorController;
use App\Http\Controllers\OwnerAuthController;


use Illuminate\Support\Facades\Route;

Route::post('/owner/login', [OwnerAuthController::class, 'login']);



// Protected owner routes - use owner-api guard
Route::middleware('auth:owner-api')->group(function () {
  Route::post('/supervisor/register', [SupervisorController::class, 'store']);
  Route::post('/owner/logout', [OwnerAuthController::class, 'logout']);
});



Route::middleware('auth:owner-api')->group(function () {

  // Owner Product Routes
  Route::prefix('owner/products')->group(function () {
    Route::get('/', [OwnerProductController::class, 'index']);
    Route::post('/add', [OwnerProductController::class, 'store']);
    Route::get('/show/{id}', [OwnerProductController::class, 'show']);
    Route::put('/update/{id}', [OwnerProductController::class, 'update']);
    Route::delete('/delete/{id}', [OwnerProductController::class, 'destroy']);


    Route::put('/{id}/stock', [OwnerProductController::class, 'updateStock']);
    Route::put('/{id}/price', [OwnerProductController::class, 'updatePrice']);
  });
});