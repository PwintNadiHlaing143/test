<?php

use App\Http\Controllers\Owner\OwnerProductController;
use App\Http\Controllers\Supervisor\SupervisorController;
use App\Http\Controllers\Owner\OwnerAuthController;
use App\Http\Controllers\Owner\OwnerShowAllEmployeesController;

use Illuminate\Support\Facades\Route;

Route::post('/owner/login', [OwnerAuthController::class, 'login']);



// Protected owner routes - use owner-api guard
Route::middleware('auth:owner-api')->group(function () {
  Route::post('/supervisor/register', [SupervisorController::class, 'store']);
  Route::get('/owner/profile', [OwnerAuthController::class, 'profile']);
  Route::put('/owner/profile', [OwnerAuthController::class, 'updateProfile']);
  Route::post('/owner/logout', [OwnerAuthController::class, 'logout']);
});



Route::middleware('auth:owner-api')->group(function () {

  // Owner Product Routes
  Route::prefix('owner')->group(function () {
    Route::get('/products', [OwnerProductController::class, 'index']);
    Route::post('/products/add', [OwnerProductController::class, 'store']);
    Route::get('/products/show/{id}', [OwnerProductController::class, 'show']);
    Route::put('/products/update/{id}', [OwnerProductController::class, 'update']);
    Route::delete('/products/delete/{id}', [OwnerProductController::class, 'destroy']);


    Route::put('/prouducts/{id}/stock', [OwnerProductController::class, 'updateStock']);
    Route::put('/proudcts/{id}/price', [OwnerProductController::class, 'updatePrice']);
  });
});




Route::prefix('owner')->group(function () {
  // Employee management routes
  Route::get('/supervisors', [OwnerShowAllEmployeesController::class, 'showSupervisors']);
  Route::get('/users', [OwnerShowAllEmployeesController::class, 'showUsers']);
  Route::get('/delivery-staff', [OwnerShowAllEmployeesController::class, 'showDeliveryStaff']);
  Route::get('/employees/stats', [OwnerShowAllEmployeesController::class, 'getOverallStats']);
});