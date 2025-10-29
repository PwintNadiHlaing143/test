<?php


use App\Http\Controllers\SupervisorController;
use Illuminate\Support\Facades\Route;


// Supervisor login (public)
Route::post('/supervisor/login', [SupervisorController::class, 'login']);

// Protected supervisor routes
Route::middleware('auth:supervisor-api')->group(function () {
  Route::post('/supervisor/logout', [SupervisorController::class, 'logout']);
  Route::get('/supervisor/profile', [SupervisorController::class, 'profile']);
});

Route::middleware('auth:supervisor-api')->prefix('supervisor')->group(
  function () {
    // Auth
    Route::post('/logout', [SupervisorController::class, 'logout']);
    Route::get('/profile', [SupervisorController::class, 'profile']);
  }
);