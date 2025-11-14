<?php


use App\Http\Controllers\OTP\OtpController;

use Illuminate\Support\Facades\Route;

Route::post('/send-otp', [OtpController::class, 'sendOtp']);
Route::post('/verify-otp', [OtpController::class, 'verifyOtp']);
Route::post('/resend-otp', [OtpController::class, 'resendOtp']);