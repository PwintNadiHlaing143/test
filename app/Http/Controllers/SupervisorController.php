<?php

namespace App\Http\Controllers;

use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SupervisorController extends Controller
{
  /**
   * Owner creates a new supervisor account.
   */
  public function store(Request $request)
  {
    $request->validate([
      'supervisor_name' => 'required|string|max:255',
      'supervisor_phone' => 'required|string|unique:supervisors,supervisor_phone',
      'supervisor_address' => 'required|string',
      'supervisor_password' => 'required|string|min:6|confirmed',
    ]);

    // Use default API guard (simpler)
    $owner = Auth::user(); // This uses the default API guard
    $ownerId = $owner ? $owner->owner_id : null; // Use owner_id instead of id

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
}
