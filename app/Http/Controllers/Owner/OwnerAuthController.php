<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owner;
use Illuminate\Support\Facades\Hash;

class OwnerAuthController extends Controller
{

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

  public function logout(Request $request)
  {

    $request->user()->token()->revoke();

    return response()->json([
      'message' => 'Logged out successfully'
    ]);
  }
}