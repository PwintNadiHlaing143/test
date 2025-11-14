<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use GuzzleHttp\Client;
use Google\Auth\Credentials\ServiceAccountCredentials;

class FCMController extends Controller
{
  public function saveToken(Request $request)
  {
    $request->validate([
      'user_id' => 'required|integer|exists:users,user_id',
      'fcm_token' => 'required|string',
    ]);

    $user = User::find($request->user_id);
    $user->fcm_token = $request->fcm_token;
    $user->save();

    return response()->json(['message' => 'FCM token saved successfully']);
  }

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

    $serviceAccountPath = storage_path('firebase/service-account.json');

    if (!file_exists($serviceAccountPath)) {
      return response()->json([
        'message' => 'Service account JSON not found',
        'path' => $serviceAccountPath
      ], 500);
    }

    // Add correct scope
    $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials(
      ['https://www.googleapis.com/auth/firebase.messaging'],
      $serviceAccountPath
    );

    $tokenArray = $credentials->fetchAuthToken();

    if (!isset($tokenArray['access_token'])) {
      return response()->json([
        'message' => 'Failed to fetch access token from service account',
        'token_response' => $tokenArray
      ], 500);
    }

    $accessToken = $tokenArray['access_token'];

    $client = new Client();
    $projectId = 'water-delivery-app-d3573'; // replace with Firebase Project ID
    $fcmUrl = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";

    $message = [
      'message' => [
        'token' => $user->fcm_token,
        'notification' => [
          'title' => $request->title,
          'body' => $request->body,
        ],
        'android' => [
          'priority' => 'HIGH'
        ],
        'apns' => [
          'headers' => ['apns-priority' => '10']
        ]
      ]
    ];

    $response = $client->post($fcmUrl, [
      'headers' => [
        'Authorization' => "Bearer $accessToken",
        'Content-Type' => 'application/json',
      ],
      'body' => json_encode($message),
    ]);

    return response()->json([
      'message' => 'Notification sent successfully',
      'fcm_response' => json_decode((string)$response->getBody(), true)
    ]);
  }
}
