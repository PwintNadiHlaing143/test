<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Township;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Laravel\Passport\Token;

class AuthController extends Controller
{
  //correct user register
  public function register(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'user_name' => 'required|string|max:100',
      'phone_number' => 'required|string|max:15|unique:users,phone_number',
      'user_password' => 'required|string|min:6|confirmed',
      'user_address' => 'required|string',
      'township_id' => 'required|exists:townships,township_id',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation error',
        'errors' => $validator->errors()
      ], 422);
    }

    $user = User::create([
      'user_name' => $request->user_name,
      'phone_number' => $request->phone_number,
      'user_password' => Hash::make($request->user_password),
      'user_address' => $request->user_address,
      'township_id' => $request->township_id,
      'current_bottles' => 0,
      'change_return' => 0.00,
      'empty_collected' => 0,
    ]);

    $tokenResult = $user->createToken('Personal Access Token');
    $token = $tokenResult->accessToken;
    $tokenInstance = $tokenResult->token;
    $tokenInstance->expires_at = Carbon::now()->addDays(30);
    $tokenInstance->save();

    return response()->json([
      'success' => true,
      'message' => 'User registered successfully',
      'data' => [
        'user' => $user,
        'access_token' => $token,
        'token_type' => 'Bearer',
        'expires_at' => Carbon::parse($tokenInstance->expires_at)->toDateTimeString(),
        'refresh_token' => $this->generateRefreshToken($user)
      ]
    ], 201);
  }

  public function login(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'phone_number' => 'required|string',
      'user_password' => 'required|string',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation error',
        'errors' => $validator->errors()
      ], 422);
    }

    $user = User::where('phone_number', $request->phone_number)->first();

    if (!$user || !Hash::check($request->user_password, $user->user_password)) {
      return response()->json([
        'success' => false,
        'message' => 'Invalid phone number or password'
      ], 401);
    }

    $tokenResult = $user->createToken('Personal Access Token');
    $token = $tokenResult->accessToken;

    $tokenInstance = $tokenResult->token;

    $tokenInstance->expires_at = Carbon::now()->addDays(30);
    $tokenInstance->save();

    $user->load('township');

    return response()->json([
      'success' => true,
      'message' => 'Login successful',
      'data' => [
        'user' => $user,
        'access_token' => $token,
        'token_type' => 'Bearer',
        'expires_at' => Carbon::parse($tokenInstance->expires_at)->toDateTimeString(),
        'refresh_token' => $this->generateRefreshToken($user)
      ]
    ], 200);
  }


  public function refreshToken(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'refresh_token' => 'required|string'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Validation error',
          'errors' => $validator->errors()
        ], 422);
      }

      // Find valid refresh token
      $token = Token::where('township_id', $request->refresh_token)
        ->where('revoked', false)
        ->where('expires_at', '>', Carbon::now())
        ->where('township_name', 'Refresh Token')
        ->first();

      if (!$token) {
        return response()->json([
          'success' => false,
          'message' => 'Invalid or expired refresh token'
        ], 401);
      }

      $user = User::find($token->user_id);

      if (!$user) {
        return response()->json([
          'success' => false,
          'message' => 'User not found'
        ], 404);
      }

      // Create new access token
      $tokenResult = $user->createToken('Personal Access Token');
      $accessToken = $tokenResult->accessToken;

      $tokenInstance = $tokenResult->token;
      $tokenInstance->expires_at = Carbon::now()->addDays(30);
      $tokenInstance->save();

      return response()->json([
        'success' => true,
        'message' => 'Token refreshed successfully',
        'data' => [
          'access_token' => $accessToken,
          'token_type' => 'Bearer',
          'expires_at' => Carbon::parse($tokenInstance->expires_at)->toDateTimeString()
        ]
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Token refresh failed',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  // Generate refresh token
  private function generateRefreshToken($user)
  {
    $refreshToken = $user->createToken('Refresh Token');
    $refreshToken->token->update([
      'expires_at' => Carbon::now()->addDays(60),
      'name' => 'Refresh Token'
    ]);

    return $refreshToken->accessToken;
  }




  public function deleteAccount(Request $request)
  {
    try {
      if (!$request->user()) {
        return response()->json([
          'success' => false,
          'message' => 'User not authenticated'
        ], 401);
      }

      $user = $request->user();

      $validator = Validator::make($request->all(), [
        'user_password' => 'required|string',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Validation error',
          'errors' => $validator->errors()
        ], 422);
      }

      if (!Hash::check($request->user_password, $user->user_password)) {
        return response()->json([
          'success' => false,
          'message' => 'Invalid password'
        ], 401);
      }


      $user->tokens()->delete();
      $user->delete();

      return response()->json([
        'success' => true,
        'message' => 'Account deleted successfully. Your order history has been preserved.'
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Account deletion failed',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function user(Request $request)
  {
    $user = $request->user()->load('township');

    return response()->json([
      'success' => true,
      'data' => [
        'user' => $user
      ]
    ], 200);
  }


  //correct logout
  public function logout(Request $request)
  {
    try {
      $user = $request->user();
      if (!$user) {
        return response()->json([
          'success' => false,
          'message' => 'User not authenticated'
        ], 401);
      }


      $request->user()->token()->revoke();

      return response()->json([
        'success' => true,
        'message' => 'Logout successful'
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Logout failed',
        'error' => $e->getMessage()
      ], 500);
    }
  }


  //correct update name
  public function updateName(Request $request)
  {
    $user = $request->user();

    $request->validate([
      'user_name' => 'required|string|max:100'
    ]);

    $user->user_name = $request->user_name;
    $user->save();

    return response()->json([
      'success' => true,
      'message' => 'Name updated successfully',
      'data' => $user
    ], 200);
  }

  //correct update address
  public function updateAddress(Request $request)
  {
    $user = $request->user();

    $request->validate([
      'user_address' => 'required|string|max:255'
    ]);

    $user->user_address = $request->user_address;
    $user->save();

    return response()->json([
      'success' => true,
      'message' => 'Address updated successfully',
      'data' => $user
    ], 200);
  }

  //correct update township
  public function updateTownship(Request $request)
  {
    $user = $request->user();

    $request->validate([
      'township_name' => 'required|string'
    ]);


    $township = Township::where('township_name', $request->township_name)->first();

    if (!$township) {
      return response()->json([
        'success' => false,
        'message' => 'Township not found'
      ], 404);
    }

    $user->township_id = $township->township_id;
    $user->save();

    $user->load('township');

    return response()->json([
      'success' => true,
      'message' => 'Township updated successfully',
      'data' => $user
    ], 200);
  }

  //correct Update password
  public function updatePassword(Request $request)
  {
    $user = $request->user();

    $request->validate([
      'current_password' => 'required',
      'new_password' => 'required|string|min:6|confirmed'
    ]);

    // Check current password
    if (!Hash::check($request->current_password, $user->user_password)) {
      return response()->json([
        'success' => false,
        'message' => 'Current password is incorrect'
      ], 401);
    }

    $user->user_password = Hash::make($request->new_password);
    $user->save();

    return response()->json([
      'success' => true,
      'message' => 'Password updated successfully'
    ], 200);
  }
}