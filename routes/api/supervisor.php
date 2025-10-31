<?php


use App\Http\Controllers\Supervisor\SupervisorController;
use App\Http\Controllers\Supervisor\SupervisorOrderController;
use Illuminate\Support\Facades\Route;



// Supervisor login (public)
Route::post('/supervisor/login', [SupervisorController::class, 'login']);


Route::middleware('auth:supervisor-api')->prefix('supervisor')->group(
  function () {
    // Auth
    Route::post('/logout', [SupervisorController::class, 'logout']);
    Route::get('/profile', [SupervisorController::class, 'profile']);

    //look order
    Route::get('/orders', [SupervisorOrderController::class, 'allOrders'])->name('supervisor.orders.all');
    Route::get('/orders/my-assigned', [SupervisorOrderController::class, 'myAssignedOrders'])->name('supervisor.orders.my-assigned');
    Route::get('/orders/pending', [SupervisorOrderController::class, 'pendingOrders'])->name('supervisor.orders.pending');
    Route::get('/orders/status/{status}', [SupervisorOrderController::class, 'ordersByStatus'])->name('supervisor.orders.status');
    Route::get('/orders/search', [SupervisorOrderController::class, 'searchOrders'])->name('supervisor.orders.search');
    Route::get('/orders/{orderId}', [SupervisorOrderController::class, 'showOrderDetail'])->name('supervisor.orders.show');
  }
);