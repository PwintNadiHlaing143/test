<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class NotificationController extends Controller
{
  public function sendNotification(Request $request)
  {
    $request->validate([
      'user_id' => 'required|integer|exists:users,user_id',
      'title' => 'required|string',
      'body' => 'required|string',
    ]);

    $user = User::find($request->user_id);

    if (!$user->fcm_token) {
      return response()->json(['message' => 'User has no FCM token'], 400);
    }

    // 🔑 Your Firebase Server Key
    $SERVER_KEY = "BENdEgXxtdcgr7-1x4_U33r3cG_33wnzvMkCiT-CnnOCHtO9leB2tkQkOLN9FBh8MfLgoLugd2lks-F0nnXtHBk";

    // 📨 Notification payload
    $data = [
      "to" => $user->fcm_token,
      "notification" => [
        "title" => $request->title,
        "body" => $request->body,
        "sound" => "default",
      ],
      "data" => [
        "click_action" => "FLUTTER_NOTIFICATION_CLICK",
      ]
    ];

    // 🚀 Send to FCM
    $response = Http::withHeaders([
      'Authorization' => 'key=' . $SERVER_KEY,
      'Content-Type' => 'application/json',
    ])->post('https://fcm.googleapis.com/fcm/send', $data);

    if ($response->successful()) {
      return response()->json(['message' => 'Notification sent successfully']);
    } else {
      return response()->json([
        'message' => 'Failed to send notification',
        'response' => $response->body()
      ], $response->status());
    }
  }
}