<?php
// routes/api.php


use App\Http\Controllers\NotificationController;

// Supervisor notification routes
Route::middleware('auth:supervisor-api')->prefix('supervisor')->group(function () {
  Route::post('/update-fcm-token', [NotificationController::class, 'updateSupervisorFcmToken']);
  Route::get('/notifications', [NotificationController::class, 'getMyNotifications']);
  Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead']);
  Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
});

// Owner notification routes
Route::middleware('auth:owner-api')->prefix('owner')->group(function () {
  Route::post('/update-fcm-token', [NotificationController::class, 'updateOwnerFcmToken']);
  Route::get('/notifications', [NotificationController::class, 'getMyNotifications']);
  Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead']);
});

// Delivery Staff notification routes
Route::middleware('auth:deliveryStaff-api')->prefix('delivery-staff')->group(function () {
  Route::post('/update-fcm-token', [NotificationController::class, 'updateStaffFcmToken']);
  Route::get('/notifications', [NotificationController::class, 'getMyNotifications']);
});

// User notification routes
Route::middleware('auth:user-api')->prefix('user')->group(function () {
  Route::post('/update-fcm-token', [NotificationController::class, 'updateUserFcmToken']);
  Route::get('/notifications', [NotificationController::class, 'getMyNotifications']);
});

// Test route (no auth required)
Route::post('/test-fcm-update', [NotificationController::class, 'testFcmUpdate']);

Route::post('/test-notification', [NotificationController::class, 'sendTestNotification']);
