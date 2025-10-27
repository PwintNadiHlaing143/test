<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OtpController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});
// Public routes (Token မလိုပါ)
// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);

// // Protected routes (Token လိုအပ်သည်)
// Route::middleware('auth:api')->group(function () {
//   // User routes
//   Route::get('/user', function (Request $request) {
//     return $request->user();
//   });

//   Route::get('/profile', [AuthController::class, 'profile']);
//   Route::post('/logout', [AuthController::class, 'logout']);

//   // Test route
//   Route::get('/test-auth', function () {
//     return response()->json([
//       'success' => true,
//       'message' => 'API authentication is working!',
//       'data' => [
//         'user' => auth()->user(),
//         'timestamp' => now()
//       ]
//     ]);
//   });
// });

// Route::get('/test', function () {
//   return response()->json([
//     'success' => true,
//     'message' => 'API is working!',
//     'data' => [
//       'version' => '1.0',
//       'timestamp' => now(),
//       'status' => 'active'
//     ]
//   ]);
// });
// Route::get('/hello', function () {
//   return response()->json([
//     'message' => 'Hello from Water Delivery API!'
//   ]);
// });

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:api')->group(function () {
  Route::get('/profile', [AuthController::class, 'profile']);
  Route::post('/logout', [AuthController::class, 'logout']);
});
Route::post('/send-otp', [OtpController::class, 'sendOtp']);
Route::post('/verify-otp', [OtpController::class, 'verifyOtp']);
Route::post('/resend-otp', [OtpController::class, 'resendOtp']);
