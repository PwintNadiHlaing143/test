<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\DeliveryGroup;
use App\Models\Township;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupervisorOrderController extends Controller
{
  //Get all orders history (deliverd,approved)for supervisor
  public function getAllOrders()
  {
    $orders = Order::with(['user', 'product'])
      ->orderBy('order_date', 'desc')
      ->paginate(10);

    return response()->json([
      'success' => true,
      'message' => 'All orders retrieved successfully',
      'orders' => $orders->items(),
      'total' => $orders->total()
    ]);
  }

  //Get pending orders for acceptance
  public function getPendingOrders()
  {
    $orders = Order::with(['user', 'product'])
      ->where('order_status', 'pending')
      ->orderBy('order_date', 'asc')
      ->paginate(10);

    $ordersData = $orders->items();

    if (empty($ordersData)) {
      return response()->json([
        'success' => true,
        'message' => 'No pending orders available',
        'orders' => []
      ]);
    }

    return response()->json([
      'success' => true,
      'message' => 'Pending orders retrieved successfully',
      'orders' => $ordersData
    ]);
  }


  //Accept single order
  public function acceptOrder($orderId)
  {
    try {
      $order = Order::where('order_id', $orderId)
        ->where('order_status', 'pending')
        ->first();

      if (!$order) {
        return response()->json([
          'success' => false,
          'message' => 'Order not found or already processed'
        ], 404);
      }

      $order->update(['order_status' => 'approved']);

      return response()->json([
        'success' => true,
        'message' => 'Order accepted successfully',
        'order' => $order
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to accept order'
      ], 500);
    }
  }

  //Accept multiple orders
  public function acceptMultipleOrders(Request $request)
  {
    try {
      $acceptedCount = 0;

      foreach ($request->order_ids as $orderId) {
        $order = Order::where('order_id', $orderId)
          ->where('order_status', 'pending')
          ->first();

        if ($order) {
          $order->update(['order_status' => 'approved']);
          $acceptedCount++;
        }
      }

      return response()->json([
        'success' => true,
        'message' => "{$acceptedCount} orders accepted successfully",
        'accepted_count' => $acceptedCount
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to accept orders'
      ], 500);
    }
  }

  //Get approved orders for route assignment
  public function getApprovedOrders()
  {
    $orders = Order::with(['user', 'product'])
      ->where('order_status', 'approved')
      ->whereDoesntHave('deliveryRoutes')
      ->orderBy('order_date', 'asc')
      ->paginate(10);

    $ordersData = $orders->items();

    if (empty($ordersData)) {
      return response()->json([
        'success' => true,
        'message' => 'No approved orders available for route assignment',
        'orders' => []
      ]);
    }

    return response()->json([
      'success' => true,
      'message' => 'Approved orders retrieved successfully',
      'orders' => $ordersData
    ]);
  }


  public function myAssignedOrders()
  {
    // Get logged in supervisor
    $supervisor = Auth::guard('supervisor-api')->user();

    if (!$supervisor) {
      return response()->json([
        'success' => false,
        'message' => 'Supervisor not logged in'
      ], 401);
    }

    // Get orders assigned to this supervisor
    $orders = Order::with(['user', 'product'])
      ->whereHas('deliveryRoutes', function ($query) use ($supervisor) {
        $query->where('supervisor_id', $supervisor->supervisor_id);
      })
      ->orderBy('order_date', 'desc')
      ->paginate(10);

    return response()->json([
      'success' => true,
      'message' => 'Assigned orders retrieved successfully',
      'orders' => $orders->items(),
      'total' => $orders->total()
    ]);
  }
}
