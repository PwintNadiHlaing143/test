<?php

use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\UserOrderController;
use App\Http\Controllers\User\UserProductController;
use Illuminate\Support\Facades\Route;

//for user
Route::post('/user/register', [AuthController::class, 'register']);
Route::post('/user/login', [AuthController::class, 'login']);

Route::middleware('auth:user-api')->prefix('user')->group(function () {
  Route::get('/profile', [AuthController::class, 'user']);
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
  Route::post('/refresh-token', [AuthController::class, 'refreshTokenSimple']);
  //user change profile detail 
  Route::put('/update-name', [AuthController::class, 'updateName']);
  Route::put('/update-address', [AuthController::class, 'updateAddress']);
  Route::put('/update-township', [AuthController::class, 'updateTownship']);
  Route::put('/update-password', [AuthController::class, 'updatePassword']);

  //user order
  Route::get('/orders', [UserOrderController::class, 'userOrders']);
  Route::post('/orders', [UserOrderController::class, 'createOrder']);
  Route::get('/order-limit-status/{productId}', [UserOrderController::class, 'getOrderLimitStatus']);
  // Route::get('/orders/{order_id}', [OrderController::class, 'showOrder']);
  Route::patch('/orders/{order_id}/status', [UserOrderController::class, 'updateStatus']); // optional
  Route::get('/orders/pending', [UserOrderController::class, 'pendingOrders']);
  Route::get('/orders/completed', [UserOrderController::class, 'completedOrders']);
  Route::get('/orders/canceled', [UserOrderController::class, 'canceledOrders']);

  //user look all products
  Route::get('/products', [UserProductController::class, 'getAllProducts']);
  Route::get('/products/search', [UserProductController::class, 'searchProducts']);
  Route::get('/products/featured', [UserProductController::class, 'getFeaturedProducts']);
  Route::get('/products/stock/{stockStatus}', [UserProductController::class, 'getProductsByStock']);
  Route::get('/products/{productId}', [UserProductController::class, 'getProduct']);



  //user see order completed detail
  Route::get('/order-history', [UserOrderController::class, 'getUserOrderHistory']);
  Route::get('/order-details/{orderId}', [UserOrderController::class, 'getOrderDetails']);
  Route::get('/bottle-statistics', [UserOrderController::class, 'getUserBottleStatistics']);
});