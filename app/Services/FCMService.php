<?php
// app/Services/FCMService.php
namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\FcmToken;

class FCMService
{
  protected $messaging;

  public function __construct()
  {
    $serviceAccountPath = storage_path('firebase/firebase-credentials.json');

    if (!file_exists($serviceAccountPath)) {
      throw new \Exception("Firebase credentials file not found at: " . $serviceAccountPath);
    }

    $factory = (new Factory)->withServiceAccount($serviceAccountPath);
    $this->messaging = $factory->createMessaging();
  }
  public function sendToUser($userId, $userType, $title, $message, $data = [])
  {
    $tokens = FcmToken::where('user_type', $userType)
      ->where('user_id', $userId)
      ->pluck('fcm_token')
      ->toArray();

    if (empty($tokens)) {
      \Log::info("No FCM tokens found for {$userType} ID: {$userId}");
      return false;
    }

    $notification = Notification::create($title, $message);

    $message = CloudMessage::new()
      ->withNotification($notification)
      ->withData(array_merge([
        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        'sound' => 'default',
        'type' => 'general',
        'user_type' => $userType
      ], $data));

    try {
      $result = $this->messaging->sendMulticast($message, $tokens);
      \Log::info("FCM sent to {$userType} ID: {$userId}", [
        'success_count' => $result->successes()->count(),
        'failure_count' => $result->failures()->count()
      ]);
      return $result;
    } catch (\Exception $e) {
      \Log::error('FCM Error: ' . $e->getMessage());
      return false;
    }
  }

  public function sendToSupervisor($supervisorId, $title, $message, $data = [])
  {
    return $this->sendToUser($supervisorId, 'supervisor', $title, $message, $data);
  }

  /**
   * Send notification to delivery staff
   */
  public function sendToStaff($staffId, $title, $message, $data = [])
  {
    return $this->sendToUser($staffId, 'staff', $title, $message, $data);
  }

  /**
   * Send notification to owner
   */
  public function sendToOwner($ownerId, $title, $message, $data = [])
  {
    return $this->sendToUser($ownerId, 'owner', $title, $message, $data);
  }

  /**
   * Send to multiple users
   */
  public function sendToMultipleUsers($userIds, $userType, $title, $message, $data = [])
  {
    $tokens = FcmToken::where('user_type', $userType)
      ->whereIn('user_id', $userIds)
      ->pluck('fcm_token')
      ->toArray();

    if (empty($tokens)) {
      return false;
    }

    $notification = Notification::create($title, $message);

    $message = CloudMessage::new()
      ->withNotification($notification)
      ->withData(array_merge([
        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        'sound' => 'default'
      ], $data));

    try {
      $result = $this->messaging->sendMulticast($message, $tokens);
      return $result;
    } catch (\Exception $e) {
      \Log::error('FCM Multicast Error: ' . $e->getMessage());
      return false;
    }
  }
}