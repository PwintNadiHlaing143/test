<?php

use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\FCMController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/api/owner.php';
require __DIR__ . '/api/otp.php';
require __DIR__ . '/api/supervisor.php';
require __DIR__ . '/api/deliveryStaff.php';
require __DIR__ . '/api/user.php';
require __DIR__ . '/api/township.php';
require __DIR__ . '/api/notification.php';



// Route::post('/save-fcm-token', [FCMController::class, 'saveToken']);
// Route::post('/send-notification', [FCMController::class, 'sendNotification']);


// api.php
Route::post('/save-fcm-token', [TestController::class, 'saveToken']);
Route::post('/send-notification', [TestController::class, 'sendNotification']);
Route::post('/user-to-supervisor', [TestController::class, 'userToSupervisor']);
Route::post('/delivery-to-supervisor', [TestController::class, 'deliveryToSupervisor']);
Route::post('/supervisor-to-delivery', [TestController::class, 'supervisorToDelivery']);
Route::get('/notifications', [TestController::class, 'getNotifications']);