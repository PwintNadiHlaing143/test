<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
  public function register(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:6|confirmed',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation error',
        'errors' => $validator->errors()
      ], 422);
    }

    $user = User::create([
      'name' => $request->name,
      'email' => $request->email,
      'password' => Hash::make($request->password),
    ]);

    $tokenResult = $user->createToken('authToken'); // Passport
    $token = $tokenResult->accessToken;

    return response()->json([
      'success' => true,
      'message' => 'User registered successfully',
      'data' => [
        'user' => $user,
        'access_token' => $token,
        'token_type' => 'Bearer',
      ]
    ], 201);
  }

  public function login(Request $request)
  {
    $credentials = $request->only('email', 'password');

    if (!Auth::attempt($credentials)) {
      return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $user = Auth::user();
    $tokenResult = $user->createToken('authToken'); // Passport
    $token = $tokenResult->accessToken;

    return response()->json([
      'success' => true,
      'message' => 'Login successful',
      'data' => [
        'user' => $user,
        'access_token' => $token,
        'token_type' => 'Bearer',
      ]
    ], 200);
  }

  public function logout(Request $request)
  {
    $request->user()->token()->revoke(); // Passport style

    return response()->json([
      'success' => true,
      'message' => 'Successfully logged out'
    ], 200);
  }
}
