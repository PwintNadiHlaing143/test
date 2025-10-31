<?php



use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;

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
  Route::get('/orders', [OrderController::class, 'userOrders']);
  Route::post('/orders', [OrderController::class, 'createOrder']);
  // Route::get('/orders/{order_id}', [OrderController::class, 'showOrder']);
  Route::patch('/orders/{order_id}/status', [OrderController::class, 'updateStatus']); // optional


  Route::get('/orders/pending', [OrderController::class, 'pendingOrders']);
  Route::get('/orders/completed', [OrderController::class, 'completedOrders']);
  Route::get('/orders/canceled', [OrderController::class, 'canceledOrders']);
});
