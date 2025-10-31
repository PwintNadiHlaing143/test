<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;

use App\Models\DeliveryStaff;
use App\Models\DeliveryGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class DeliveryStaffAuthController extends Controller
{

  public function registerBySupervisor(Request $request)
  {
    $supervisor = $request->user('supervisor-api');

    $validator = Validator::make($request->all(), [
      'staff_name' => 'required|string|max:100',
      'staff_phone' => 'required|string|max:15|unique:delivery_staff',
      'staff_password' => 'required|string|min:6',
      'staff_address' => 'required|string',
      'group_id' => 'required|exists:delivery_group,group_id',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'error' => $validator->errors()
      ], 422);
    }

    // Check if the group belongs to this supervisor
    $group = DeliveryGroup::where('group_id', $request->group_id)
      ->where('supervisor_id', $supervisor->supervisor_id)
      ->first();

    if (!$group) {
      return response()->json([
        'error' => 'You can only assign staff to your own delivery groups'
      ], 403);
    }

    $deliveryStaff = DeliveryStaff::create([
      'staff_name' => $request->staff_name,
      'staff_phone' => $request->staff_phone,
      'staff_password' => Hash::make($request->staff_password),
      'staff_address' => $request->staff_address,
      'group_id' => $request->group_id,
      'staff_status' => true,
    ]);

    return response()->json([
      'message' => 'Delivery staff registered successfully',
      'staff' => $deliveryStaff->load('deliveryGroup')
    ], 201);
  }


  //staff login
  public function login(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'staff_phone' => 'required|string',
      'staff_password' => 'required|string',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    // Find staff by phone
    $staff = DeliveryStaff::where('staff_phone', $request->staff_phone)->first();

    if (!$staff || !Hash::check($request->staff_password, $staff->staff_password)) {
      return response()->json(['error' => 'Invalid phone or password'], 401);
    }


    Auth::shouldUse('deliveryStaff-api');


    $token = $staff->createToken('DeliveryStaffToken')->accessToken;

    return response()->json([
      'message' => 'Login successful',
      'staff' => $staff,
      'token' => $token
    ], 200);
  }
  //for logout staff
  public function logout(Request $request)
  {
    $user = Auth::guard('deliveryStaff-api')->user();

    if ($user && $user->token()) {
      $user->token()->revoke();
    }

    return response()->json(['message' => 'Logout successful']);
  }

  //for see profile
  public function profile(Request $request)
  {
    $user = Auth::guard('deliveryStaff-api')->user();

    if (!$user) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    return response()->json($user);
  }
}