<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Supervisor;
use App\Models\Owner;
use App\Models\DeliveryStaff;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
  protected $messaging;

  public function __construct()
  {
    $this->firebase = (new Factory())
      ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')))
      ->createMessaging();
  }

  /**
   * Send notification to any user type
   */
  public function sendNotificationToUser(Request $request)
  {
    $request->validate([
      'user_id' => 'required|exists:users,id',
      'title' => 'required|string',
      'body' => 'required|string',
    ]);

    $user = \App\Models\User::find($request->user_id);

    // TEMPORARY FOR TESTING ONLY
    // Skip FCM token check
    // if (!$user->fcm_token) {
    //     return response()->json(['message' => 'User has no FCM token'], 400);
    // }

    // Simulate sending notification
    return response()->json([
      'message' => 'Notification logic passed successfully',
      'user_id' => $user->id,
      'title' => $request->title,
      'body' => $request->body,
    ]);
  }

  /**
   * Send push notification via Firebase for specific user type
   */
  public function sendPushNotification($userId, $userType, $title, $body, $data = [])
  {
    try {
      $user = null;
      $fcmToken = null;

      // Get user based on type
      switch ($userType) {
        case 'user':
          $user = User::find($userId);
          break;
        case 'supervisor':
          $user = Supervisor::find($userId);
          break;
        case 'owner':
          $user = Owner::find($userId);
          break;
        case 'delivery_staff':
          $user = DeliveryStaff::find($userId);
          break;
      }

      if (!$user || !$user->fcm_token) {
        Log::warning("FCM token missing for $userType: " . $userId);
        return false;
      }

      // Create Firebase message
      $message = CloudMessage::withTarget('token', $user->fcm_token)
        ->withNotification(FirebaseNotification::create($title, $body))
        ->withData(array_merge([
          'title' => $title,
          'body' => $body,
          'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
          'notification_id' => $data['notification_id'] ?? '',
          'user_type' => $userType,
          'type' => $data['type'] ?? 'general'
        ], $data));

      // Send notification
      $this->messaging->send($message);

      Log::info("Firebase notification sent to $userType: $userId");
      return true;
    } catch (\Exception $e) {
      Log::error('Firebase Error: ' . $e->getMessage());

      // Remove invalid token
      if (str_contains($e->getMessage(), 'registration-token-not-registered')) {
        if ($user) {
          $user->update(['fcm_token' => null]);
        }
      }

      return false;
    }
  }
}
