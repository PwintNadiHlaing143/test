<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



require __DIR__ . '/api/owner.php';
require __DIR__ . '/api/otp.php';
require __DIR__ . '/api/supervisor.php';
require __DIR__ . '/api/deliveryStaff.php';
require __DIR__ . '/api/user.php';
require __DIR__ . '/api/township.php';






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
