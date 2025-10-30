<?php



use App\Http\Controllers\AuthController;

use Illuminate\Support\Facades\Route;

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//   return $request->user();
// });


//for user
Route::post('/user/register', [AuthController::class, 'register']);
Route::post('/user/login', [AuthController::class, 'login']);

Route::middleware('auth:user-api')->prefix('user')->group(function () {
  Route::get('/profile', [AuthController::class, 'user']);
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);
  Route::post('/refresh-token', [AuthController::class, 'refreshTokenSimple']);

  // ✅ Separate update routes
  Route::put('/update-name', [AuthController::class, 'updateName']);
  Route::put('/update-address', [AuthController::class, 'updateAddress']);
  Route::put('/update-township', [AuthController::class, 'updateTownship']);
  Route::put('/update-password', [AuthController::class, 'updatePassword']);
});