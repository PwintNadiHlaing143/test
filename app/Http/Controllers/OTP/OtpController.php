<?php

namespace App\Http\Controllers\OTP;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OtpController extends Controller
{
  // Generate OTP
  public function sendOtp(Request $request)
  {
    $request->validate([
      'phone' => 'required|string'
    ]);

    // Generate random 
    $otp = rand(100000, 999999);

    // Expire time 1 minute
    $expiresAt = Carbon::now()->addMinute();

    // Save or update OTP record
    try {
      DB::table('otps')->updateOrInsert(
        ['phone' => $request->phone],
        [
          'otp_code' => $otp,
          'expires_at' => $expiresAt,
          'updated_at' => now(),
          'created_at' => now(),
        ]
      );
    } catch (\Exception $e) {
      return response()->json([
        'status' => false,
        'message' => 'Failed to store OTP',
        'error' => $e->getMessage()
      ], 500);
    }


    return response()->json([
      'status' => true,
      'message' => 'OTP generated successfully',
      'otp_code' => $otp,
      'expires_in' => '1 minute',
    ]);
  }

  // Verify OTP
  public function verifyOtp(Request $request)
  {
    $request->validate([
      'phone' => 'required|string',
      'otp' => 'required|string'
    ]);

    $otpData = DB::table('otps')->where('phone', $request->phone)->first();

    if (!$otpData) {
      return response()->json(['status' => false, 'message' => 'No OTP found'], 400);
    }

    // Check if expired
    if (Carbon::now()->greaterThan($otpData->expires_at)) {
      DB::table('otps')->where('phone', $request->phone)->delete();
      return response()->json(['status' => false, 'message' => 'OTP expired'], 400);
    }

    // Check code
    if ($otpData->otp_code != $request->otp) {
      return response()->json(['status' => false, 'message' => 'Invalid OTP'], 400);
    }

    // OTP verified 
    DB::table('otps')->where('phone', $request->phone)->delete();

    return response()->json(['status' => true, 'message' => 'OTP verified successfully']);
  }

  // Resend OTP 
  public function resendOtp(Request $request)
  {
    $request->validate([
      'phone' => 'required|string'
    ]);

    $phone = $request->phone;


    $newOtp = rand(100000, 999999);
    $expiresAt = Carbon::now()->addMinute();

    try {
      DB::table('otps')->updateOrInsert(
        ['phone' => $phone],
        [
          'otp_code' => $newOtp,
          'expires_at' => $expiresAt,
          'updated_at' => now(),
          'created_at' => now(),
        ]
      );
    } catch (\Exception $e) {
      return response()->json([
        'status' => false,
        'message' => 'Failed to resend OTP',
        'error' => $e->getMessage()
      ], 500);
    }

    return response()->json([
      'status' => true,
      'message' => 'New OTP generated successfully',
      'otp_code' => $newOtp,
      'expires_in' => '1 minute',
    ]);
  }
}