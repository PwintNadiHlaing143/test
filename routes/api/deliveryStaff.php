<?php


use App\Http\Controllers\Delivery\DeliveryStaffAuthController;
use App\Http\Controllers\Delivery\DeliveryStaffOrderController;

use App\Http\Controllers\Supervisor\SupervisorController;

use Illuminate\Support\Facades\Route;


Route::middleware('auth:supervisor-api')->prefix('supervisor')->group(function () {


  // Delivery Staff Management
  Route::post('/staff/register', [DeliveryStaffAuthController::class, 'registerBySupervisor']);
  Route::get('/staff', [DeliveryStaffAuthController::class, 'getMyStaff']);
  Route::get('/groups', [DeliveryStaffAuthController::class, 'getMyGroups']);
  Route::put('/staff/{id}/status', [DeliveryStaffAuthController::class, 'updateStaffStatus']);
});

//delivery staff login
Route::prefix('delivery-staff')->group(function () {

  Route::post('/login', [DeliveryStaffAuthController::class, 'login']);
});


//delivery staff auth
Route::middleware('auth:deliveryStaff-api')->prefix('delivery-staff')->group(function () {

  Route::post('/logout', [DeliveryStaffAuthController::class, 'logout']);
  Route::get('/profile', [DeliveryStaffAuthController::class, 'profile']);
  Route::get('/view-orders', [DeliveryStaffOrderController::class, 'viewAssignedOrders']);
  Route::post('/update-order-status/{routeId}/{orderId}', [DeliveryStaffOrderController::class, 'updateDeliveryStatus']);
  Route::get('/view-completed-orders', [DeliveryStaffOrderController::class, 'viewCompletedOrders']);
  Route::post('/reorder', [DeliveryStaffOrderController::class, 'reorderForUser']);
});