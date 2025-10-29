<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TownshipController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\OwnerAuthController;
use App\Http\Controllers\DeliveryStaffAuthController;
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







Route::middleware('auth:supervisor-api')->prefix('supervisor')->group(function () {
  // Auth
  Route::post('/logout', [SupervisorController::class, 'logout']);
  Route::get('/profile', [SupervisorController::class, 'profile']);

  // Delivery Staff Management
  Route::post('/staff/register', [DeliveryStaffAuthController::class, 'registerBySupervisor']);
  Route::get('/staff', [DeliveryStaffAuthController::class, 'getMyStaff']);
  Route::get('/groups', [DeliveryStaffAuthController::class, 'getMyGroups']);
  Route::put('/staff/{id}/status', [DeliveryStaffAuthController::class, 'updateStaffStatus']);

  // Additional supervisor routes
  Route::get('/dashboard', [SupervisorController::class, 'dashboard']);
});



Route::prefix('staff')->group(function () {
  Route::post('/login', [DeliveryStaffAuthController::class, 'login']);

  Route::middleware('auth:deliveryStaff-api')->group(function () {
    Route::post('/logout', [DeliveryStaffAuthController::class, 'logout']);
    Route::get('/profile', [DeliveryStaffAuthController::class, 'profile']);
  });
});

/*
|--------------------------------------------------------------------------
| Test Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:supervisor-api')->get('/test-supervisor', function (Request $request) {
  return response()->json([
    'message' => 'Supervisor API is working!',
    'supervisor' => $request->user()
  ]);
});

Route::middleware('auth:deliveryStaff-api')->get('/test-delivery-staff', function (Request $request) {
  return response()->json([
    'message' => 'Delivery Staff API is working!',
    'staff' => $request->user()
  ]);
});