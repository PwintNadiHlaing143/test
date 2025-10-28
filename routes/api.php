<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TownshipController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\OwnerAuthController;
use App\Http\Controllers\OtpController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});
//for user
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
  Route::get('/profile', [AuthController::class, 'profile']);
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
  Route::post('/refresh-token', [AuthController::class, 'refreshTokenSimple']);
});
Route::post('/send-otp', [OtpController::class, 'sendOtp']);
Route::post('/verify-otp', [OtpController::class, 'verifyOtp']);
Route::post('/resend-otp', [OtpController::class, 'resendOtp']);




// for townships
Route::get('/townships', [TownshipController::class, 'index']);
Route::get('/townships/{id}', [TownshipController::class, 'show']);
Route::get('/townships/search/{name}', [TownshipController::class, 'search']);
// Supervisor login (public)
Route::post('/supervisor/login', [SupervisorController::class, 'login']);



// Owner authentication routes
Route::post('/owner/login', [OwnerAuthController::class, 'login']);

// Protected owner routes - use owner-api guard
Route::middleware('auth:owner-api')->group(function () {
  Route::post('/supervisors', [SupervisorController::class, 'store']);
  Route::post('/owner/logout', [OwnerAuthController::class, 'logout']);
});

// Supervisor authentication routes  
Route::post('/supervisor/login', [SupervisorController::class, 'login']);

// Protected supervisor routes
Route::middleware('auth:supervisor-api')->group(function () {
  Route::post('/supervisor/logout', [SupervisorController::class, 'logout']);
  Route::get('/supervisor/profile', [SupervisorController::class, 'profile']);
});
