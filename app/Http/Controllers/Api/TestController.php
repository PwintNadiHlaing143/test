<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Supervisor;
use App\Models\DeliveryStaff;
use App\Models\Notification;
use Illuminate\Support\Facades\Http; // Add this import

class TestController extends Controller
{
  // FCM Token သိမ်းဆည်းခြင်း
  public function saveToken(Request $request)
  {
    $request->validate([
      'user_id' => 'required|integer',
      'user_type' => 'required|string|in:user,supervisor,delivery',
      'fcm_token' => 'required|string',
    ]);

    $user = $this->getUserByType($request->user_type, $request->user_id);

    if ($user) {
      $user->fcm_token = $request->fcm_token;
      $user->save();

      return response()->json(['message' => 'FCM token saved successfully']);
    }

    return response()->json(['message' => 'User not found'], 404);
  }

  // General Notification ပို့ခြင်း
  public function sendNotification(Request $request)
  {
    $request->validate([
      'from_user_id' => 'required|integer',
      'from_user_type' => 'required|string|in:user,supervisor,delivery',
      'to_user_id' => 'required|integer',
      'to_user_type' => 'required|string|in:user,supervisor,delivery',
      'title' => 'required|string',
      'body' => 'required|string',
      'notification_type' => 'required|string',
    ]);

    // Receiver user ရှာမယ်
    $toUser = $this->getUserByType($request->to_user_type, $request->to_user_id);

    if (!$toUser || !$toUser->fcm_token) {
      return response()->json(['message' => 'Receiver has no FCM token'], 400);
    }

    // Notification Database ထဲမှာ သိမ်းမယ်
    $notification = Notification::create([
      'supervisor_id' => $request->from_user_id, // Use supervisor_id field for from_user_id
      'not_message' => $request->body,
      'created_at' => now(),
      'B_read' => false,
      'create0_by' => $request->from_user_id,
    ]);

    // FCM Server Key
    $SERVER_KEY = "BENdEgXxtdcgr7-1x4_U33r3cG_33wnzvMkCiT-CnnOCHtO9leB2tkQkOLN9FBh8MfLgoLugd2lks-F0nnXtHBk";

    $data = [
      "to" => $toUser->fcm_token,
      "notification" => [
        "title" => $request->title,
        "body" => $request->body,
        "sound" => "default",
      ],
      "data" => [
        "click_action" => "FLUTTER_NOTIFICATION_CLICK",
        "notification_id" => $notification->notification_id,
        "type" => $request->notification_type,
        "from_user_id" => $request->from_user_id,
        "from_user_type" => $request->from_user_type,
      ]
    ];

    // FCM ကို Request ပို့မယ်
    $response = Http::withHeaders([
      'Authorization' => 'key=' . $SERVER_KEY,
      'Content-Type' => 'application/json',
    ])->post('https://fcm.googleapis.com/fcm/send', $data);

    if ($response->successful()) {
      return response()->json([
        'message' => 'Notification sent successfully',
        'notification_id' => $notification->notification_id
      ]);
    } else {
      return response()->json([
        'message' => 'Failed to send notification',
        'response' => $response->body()
      ], $response->status());
    }
  }

  // User to Supervisor Notification
  public function userToSupervisor(Request $request)
  {
    $request->validate([
      'user_id' => 'required|integer|exists:users,user_id',
      'supervisor_id' => 'required|integer|exists:supervisors,supervisor_id',
      'message' => 'required|string',
      'title' => 'required|string',
    ]);

    $supervisor = Supervisor::find($request->supervisor_id);

    if (!$supervisor || !$supervisor->fcm_token) {
      return response()->json(['message' => 'Supervisor has no FCM token'], 400);
    }

    // Notification သိမ်းမယ်
    $notification = Notification::create([
      'supervisor_id' => $request->user_id, // user_id ကို supervisor_id field မှာ သိမ်း
      'not_message' => $request->message,
      'created_at' => now(),
      'B_read' => false,
      'create0_by' => $request->user_id,
    ]);

    $SERVER_KEY = "BENdEgXxtdcgr7-1x4_U33r3cG_33wnzvMkCiT-CnnOCHtO9leB2tkQkOLN9FBh8MfLgoLugd2lks-F0nnXtHBk";

    $data = [
      "to" => $supervisor->fcm_token,
      "notification" => [
        "title" => $request->title,
        "body" => $request->message,
        "sound" => "default",
      ],
      "data" => [
        "click_action" => "FLUTTER_NOTIFICATION_CLICK",
        "notification_id" => $notification->notification_id,
        "from_user_id" => $request->user_id,
        "from_user_type" => "user",
        "type" => "user_to_supervisor"
      ]
    ];

    $response = Http::withHeaders([
      'Authorization' => 'key=' . $SERVER_KEY,
      'Content-Type' => 'application/json',
    ])->post('https://fcm.googleapis.com/fcm/send', $data);

    if ($response->successful()) {
      return response()->json([
        'message' => 'Notification sent successfully',
        'notification_id' => $notification->notification_id
      ]);
    } else {
      return response()->json([
        'message' => 'Failed to send notification',
        'response' => $response->body()
      ], 500);
    }
  }

  // Delivery to Supervisor Notification
  public function deliveryToSupervisor(Request $request)
  {
    $request->validate([
      'delivery_id' => 'required|integer|exists:delivery_staff,staff_id',
      'supervisor_id' => 'required|integer|exists:supervisors,supervisor_id',
      'message' => 'required|string',
      'title' => 'required|string',
    ]);

    $supervisor = Supervisor::find($request->supervisor_id);

    if (!$supervisor || !$supervisor->fcm_token) {
      return response()->json(['message' => 'Supervisor has no FCM token'], 400);
    }

    // Notification သိမ်းမယ်
    $notification = Notification::create([
      'supervisor_id' => $request->delivery_id, // delivery_id ကို supervisor_id field မှာ သိမ်း
      'not_message' => $request->message,
      'created_at' => now(),
      'B_read' => false,
      'create0_by' => $request->delivery_id,
    ]);

    $SERVER_KEY = "BENdEgXxtdcgr7-1x4_U33r3cG_33wnzvMkCiT-CnnOCHtO9leB2tkQkOLN9FBh8MfLgoLugd2lks-F0nnXtHBk";

    $data = [
      "to" => $supervisor->fcm_token,
      "notification" => [
        "title" => $request->title,
        "body" => $request->message,
        "sound" => "default",
      ],
      "data" => [
        "click_action" => "FLUTTER_NOTIFICATION_CLICK",
        "notification_id" => $notification->notification_id,
        "from_user_id" => $request->delivery_id,
        "from_user_type" => "delivery",
        "type" => "delivery_to_supervisor"
      ]
    ];

    $response = Http::withHeaders([
      'Authorization' => 'key=' . $SERVER_KEY,
      'Content-Type' => 'application/json',
    ])->post('https://fcm.googleapis.com/fcm/send', $data);

    if ($response->successful()) {
      return response()->json([
        'message' => 'Notification sent successfully',
        'notification_id' => $notification->notification_id
      ]);
    } else {
      return response()->json([
        'message' => 'Failed to send notification',
        'response' => $response->body()
      ], 500);
    }
  }

  // Supervisor to Delivery Notification
  public function supervisorToDelivery(Request $request)
  {
    $request->validate([
      'supervisor_id' => 'required|integer|exists:supervisors,supervisor_id',
      'delivery_id' => 'required|integer|exists:delivery_staff,staff_id',
      'message' => 'required|string',
      'title' => 'required|string',
    ]);

    $delivery = DeliveryStaff::find($request->delivery_id);

    if (!$delivery || !$delivery->fcm_token) {
      return response()->json(['message' => 'Delivery staff has no FCM token'], 400);
    }

    // Notification သိမ်းမယ်
    $notification = Notification::create([
      'supervisor_id' => $request->supervisor_id,
      'not_message' => $request->message,
      'created_at' => now(),
      'B_read' => false,
      'create0_by' => $request->supervisor_id,
    ]);

    $SERVER_KEY = "BENdEgXxtdcgr7-1x4_U33r3cG_33wnzvMkCiT-CnnOCHtO9leB2tkQkOLN9FBh8MfLgoLugd2lks-F0nnXtHBk";

    $data = [
      "to" => $delivery->fcm_token,
      "notification" => [
        "title" => $request->title,
        "body" => $request->message,
        "sound" => "default",
      ],
      "data" => [
        "click_action" => "FLUTTER_NOTIFICATION_CLICK",
        "notification_id" => $notification->notification_id,
        "from_user_id" => $request->supervisor_id,
        "from_user_type" => "supervisor",
        "type" => "supervisor_to_delivery"
      ]
    ];

    $response = Http::withHeaders([
      'Authorization' => 'key=' . $SERVER_KEY,
      'Content-Type' => 'application/json',
    ])->post('https://fcm.googleapis.com/fcm/send', $data);

    if ($response->successful()) {
      return response()->json([
        'message' => 'Notification sent successfully',
        'notification_id' => $notification->notification_id
      ]);
    } else {
      return response()->json([
        'message' => 'Failed to send notification',
        'response' => $response->body()
      ], 500);
    }
  }

  // User type အလိုက် user ရှာခြင်း
  private function getUserByType($type, $id)
  {
    switch ($type) {
      case 'user':
        return User::find($id);
      case 'supervisor':
        return Supervisor::find($id);
      case 'delivery':
        return DeliveryStaff::find($id);
      default:
        return null;
    }
  }

  // User ၏ Notifications များကိုရယူခြင်း
  public function getUserNotifications(Request $request)
  {
    $request->validate([
      'user_id' => 'required|integer',
      'user_type' => 'required|string',
    ]);

    $notifications = Notification::where('supervisor_id', $request->user_id)
      ->orderBy('created_at', 'desc')
      ->get();

    return response()->json(['notifications' => $notifications]);
  }

  // Notification ဖတ်ပြီးသားအဖြစ် Mark လုပ်ခြင်း
  public function markAsRead(Request $request)
  {
    $request->validate([
      'notification_id' => 'required|integer|exists:notifications,notification_id',
    ]);

    $notification = Notification::find($request->notification_id);
    $notification->B_read = true;
    $notification->save();

    return response()->json(['message' => 'Notification marked as read']);
  }
}
