<?php

use App\Http\Controllers\Supervisor\DeliveryGroupController;
use App\Http\Controllers\Supervisor\SupervisorController;
use App\Http\Controllers\Supervisor\SupervisorOrderController;
use App\Http\Controllers\Supervisor\SupervisorProductController;
use App\Http\Controllers\Supervisor\DeliveryRouteController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// Supervisor login (public)
Route::post('/supervisor/login', [SupervisorController::class, 'login']);

Route::middleware('auth:supervisor-api')->prefix('supervisor')->group(function () {
  // Auth
  Route::post('/logout', [SupervisorController::class, 'logout']);
  Route::get('/profile', [SupervisorController::class, 'profile']);

  Route::get('/test-service-account', [NotificationController::class, 'testServiceAccount']);
  Route::post('/test-fcm-send', [NotificationController::class, 'testFcmSend']);
  Route::post('/send-to-user', [NotificationController::class, 'sendToUser']);
  //supervisor see accepted orders
  Route::get('/orders', [SupervisorOrderController::class, 'getAllOrders']);
  Route::get('/pending-orders', [SupervisorOrderController::class, 'getPendingOrders']);

  Route::post('/accept-order/{orderId}', [SupervisorOrderController::class, 'acceptOrder']);
  Route::post('/accept-multiple-orders', [SupervisorOrderController::class, 'acceptMultipleOrders']);



  Route::get('/approved-orders', [SupervisorOrderController::class, 'getApprovedOrders']);
  Route::get('/my-assigned-orders', [SupervisorOrderController::class, 'myAssignedOrders']);




  Route::get('/orders/search', [SupervisorOrderController::class, 'searchOrders']);
  Route::get('/orders/{orderId}', [SupervisorOrderController::class, 'showOrderDetail']);






  // Products
  Route::get('/products', [SupervisorProductController::class, 'index']);
  Route::get('/products/{id}', [SupervisorProductController::class, 'show']);
  Route::put('/products/{id}', [SupervisorProductController::class, 'update']);
  Route::patch('/products/{id}/stock', [SupervisorProductController::class, 'updateStock']);
  // Add these routes inside your supervisor group
  Route::post('/products/{id}/upload-image', [SupervisorProductController::class, 'uploadImage']);
  Route::delete('/products/{id}/image', [SupervisorProductController::class, 'deleteImage']);





  Route::get('/assignment-options', [SupervisorOrderController::class, 'getAssignmentOptions']);



  //supervisor  Route Management 
  Route::get('/routes', [DeliveryRouteController::class, 'getAllRoutes']);
  Route::get('/route-details/{routeId}', [DeliveryRouteController::class, 'getRouteDetails']);
  Route::get('/route-stats', [DeliveryRouteController::class, 'getRouteStats']);


  //create route see according orderId
  Route::post('/create-route', [DeliveryRouteController::class, 'createRoute']);
  Route::put('/update-route-status/{routeId}', [DeliveryRouteController::class, 'updateRouteStatus']);
  Route::delete('/delete-route/{routeId}', [DeliveryRouteController::class, 'deleteRoute']);
});



// Delivery Groups Management
Route::prefix('delivery-groups')->group(function () {
  Route::get('/', [DeliveryGroupController::class, 'index']);
  Route::get('/{groupId}', [DeliveryGroupController::class, 'show']);
  Route::post('/{groupId}/assign-staff', [DeliveryGroupController::class, 'assignStaff']);
  Route::delete('/{groupId}/staff/{staffId}', [DeliveryGroupController::class, 'removeStaff']);
  Route::get('/{groupId}/stats', [DeliveryGroupController::class, 'getGroupStats']);
});