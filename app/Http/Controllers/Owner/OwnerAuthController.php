<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owner;
use Illuminate\Support\Facades\Hash;

class OwnerAuthController extends Controller
{

  //correct owner login

  public function login(Request $request)
  {
    $request->validate([
      'owner_phone' => 'required|string',
      'owner_password' => 'required|string',
    ]);

    $owner = Owner::where('owner_phone', $request->owner_phone)->first();

    if (!$owner || !Hash::check($request->owner_password, $owner->owner_password)) {
      return response()->json([
        'message' => 'Invalid credentials'
      ], 401);
    }

    // Passport token create
    $token = $owner->createToken('OwnerToken')->accessToken;

    return response()->json([
      'message' => 'Owner login successful',
      'token' => $token,
      'data' => $owner
    ]);
  }

  //correct owner logout
  public function logout(Request $request)
  {

    $request->user()->token()->revoke();

    return response()->json([
      'message' => 'Logged out successfully'
    ]);
  }
  // Owner Profile Function
  public function profile(Request $request)
  {
    $owner = $request->user();

    return response()->json([
      'message' => 'Owner profile retrieved successfully',
      'data' => $owner
    ]);
  }

  // Owner Profile Update Function
  public function updateProfile(Request $request)
  {
    $owner = $request->user();

    $request->validate([
      'owner_name' => 'sometimes|string|max:255',
      'owner_email' => 'sometimes|email|unique:owners,owner_email,' . $owner->owner_id . ',owner_id',
      'owner_address' => 'sometimes|string',
      'shop_name' => 'sometimes|string',
      'shop_address' => 'sometimes|string',
    ]);

    $owner->update($request->all());

    return response()->json([
      'message' => 'Profile updated successfully',
      'data' => $owner
    ]);
  }
}