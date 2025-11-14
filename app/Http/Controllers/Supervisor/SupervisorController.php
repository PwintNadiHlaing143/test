<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SupervisorController extends Controller
{


  //supervisor register by owner

  public function store(Request $request)
  {
    $request->validate([
      'supervisor_name' => 'required|string|max:255',
      'supervisor_phone' => 'required|string|unique:supervisors,supervisor_phone',
      'supervisor_address' => 'required|string',
      'supervisor_password' => 'required|string|min:6|confirmed',
    ]);

    $owner = Auth::user();
    $ownerId = $owner ? $owner->owner_id : null;

    if (!$ownerId) {
      return response()->json([
        'message' => 'Owner not authenticated',
        'debug' => [
          'authenticated' => Auth::check(),
          'user' => Auth::user() ? Auth::user()->only('owner_id', 'owner_name') : null
        ]
      ], 401);
    }

    $supervisor = Supervisor::create([
      'owner_id' => $ownerId,
      'supervisor_name' => $request->supervisor_name,
      'supervisor_phone' => $request->supervisor_phone,
      'supervisor_address' => $request->supervisor_address,
      'supervisor_password' => Hash::make($request->supervisor_password),
      'supervisor_status' => true,
    ]);

    return response()->json([
      'message' => 'Supervisor account created successfully',
      'data' => $supervisor,
    ], 201);
  }

  //correct supervisor login
  public function login(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'supervisor_phone' => 'required|string',
      'supervisor_password' => 'required|string',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'message' => 'Validation failed',
        'errors' => $validator->errors()
      ], 422);
    }


    $supervisor = Supervisor::where('supervisor_phone', $request->supervisor_phone)
      ->where('supervisor_status', true)
      ->first();


    if (!$supervisor || !Hash::check($request->supervisor_password, $supervisor->supervisor_password)) {
      return response()->json([
        'message' => 'Invalid credentials or account inactive'
      ], 401);
    }


    $token = $supervisor->createToken('SupervisorToken')->accessToken;

    return response()->json([
      'message' => 'Login successful',
      'access_token' => $token,
      'token_type' => 'Bearer',
      'supervisor' => [
        'supervisor_id' => $supervisor->supervisor_id,
        'supervisor_name' => $supervisor->supervisor_name,
        'supervisor_phone' => $supervisor->supervisor_phone,
        'supervisor_address' => $supervisor->supervisor_address,
        'owner_id' => $supervisor->owner_id,
      ]
    ]);
  }

  //supervisor logout
  public function logout(Request $request)
  {

    $request->user()->token()->revoke();

    return response()->json([
      'message' => 'Successfully logged out'
    ]);
  }

  //supervisor profile
  public function profile(Request $request)
  {
    $supervisor = Auth::guard('supervisor-api')->user();

    return response()->json([
      'supervisor' => [
        'supervisor_id' => $supervisor->supervisor_id,
        'supervisor_name' => $supervisor->supervisor_name,
        'supervisor_phone' => $supervisor->supervisor_phone,
        'supervisor_address' => $supervisor->supervisor_address,
        'supervisor_status' => $supervisor->supervisor_status,
        'owner_id' => $supervisor->owner_id,
        'created_at' => $supervisor->created_at,
      ]
    ]);
  }
}