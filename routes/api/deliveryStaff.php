<?php


use App\Http\Controllers\Delivery\DeliveryStaffAuthController;
use App\Http\Controllers\Supervisor\SupervisorController;

use Illuminate\Support\Facades\Route;


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


//delivery staff route
Route::prefix('staff')->group(function () {
  Route::post('/login', [DeliveryStaffAuthController::class, 'login']);

  Route::middleware('auth:deliveryStaff-api')->group(function () {
    Route::post('/logout', [DeliveryStaffAuthController::class, 'logout']);
    Route::get('/profile', [DeliveryStaffAuthController::class, 'profile']);
  });
});