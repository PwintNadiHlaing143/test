<?php
// app/Http/Controllers/Supervisor/SupervisorOrderController.php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\DeliveryRoute;
use Illuminate\Http\Request;

class SupervisorOrderController extends Controller
{

  //correct supervisor look they assign order
  // public function myAssignedOrders()
  // {
  //   $supervisorId = auth()->id();

  //   $orders = Orders::with(['user', 'product'])
  //     ->whereHas('deliveryRoutes', function ($query) use ($supervisorId) {
  //       $query->where('supervisor_id', $supervisorId);
  //     })
  //     ->orderBy('order_date', 'desc')
  //     ->paginate(10);

  //   $ordersData = $orders->items();

  //   if (empty($ordersData)) {
  //     return response()->json([
  //       'success' => true,
  //       'message' => 'No assigned orders found for you.',
  //       'orders' => [],

  //     ]);
  //   }

  //   return response()->json([
  //     'success' => true,
  //     'message' => 'Assigned orders retrieved successfully.',
  //     'orders' => $ordersData,
  //     'total' => $orders->total(),
  //     'skip' => ($orders->currentPage() - 1) * $orders->perPage(),
  //     'limit' => $orders->perPage(),


  //   ]);
  // }

  public function myAssignedOrders()
  {
    $supervisorId = auth()->id();

    $orders = Orders::with(['user' => function ($query) {
      $query->withTrashed(); // ✅ Deleted users included
    }, 'product'])
      ->whereHas('deliveryRoutes', function ($query) use ($supervisorId) {
        $query->where('supervisor_id', $supervisorId);
      })
      ->orderBy('order_date', 'desc')
      ->paginate(10);

    return response()->json([
      'success' => true,
      'message' => 'Assigned orders retrieved successfully.',
      'orders' => $orders
    ]);
  }

  //all supervisor can look all order
  // public function allOrders()
  // {
  //   $orders = Orders::with(['user', 'product', 'deliveryRoutes'])
  //     ->orderBy('order_date', 'desc')
  //     ->paginate(10);

  //   // Use isEmpty()
  //   if ($orders->isEmpty()) {
  //     return response()->json([
  //       'success' => true,
  //       'message' => 'No orders found in the system.',
  //       'orders' => $orders
  //     ]);
  //   }

  //   return response()->json([
  //     'success' => true,
  //     'message' => 'All orders retrieved successfully.',
  //     'orders' => $orders
  //   ]);
  // }
  public function allOrders()
  {
    $orders = Orders::with(['user' => function ($query) {
      $query->withTrashed(); // ✅ Deleted users included
    }, 'product', 'deliveryRoutes'])
      ->orderBy('order_date', 'desc')
      ->paginate(10);

    return response()->json([
      'success' => true,
      'message' => 'All orders retrieved successfully.',
      'orders' => $orders
    ]);
  }

  //correct pending order
  public function pendingOrders()
  {
    $orders = Orders::with(['user', 'product'])
      ->whereDoesntHave('deliveryRoutes')
      ->where('order_status', 'pending')
      ->orderBy('order_date', 'asc')
      ->paginate(10);
    $ordersData = $orders->items();
    // Use isEmpty()
    if (empty($ordersData)) {
      return response()->json([
        'success' => true,
        'message' => 'No pending orders available for assignment.',
        'orders' => [],

      ]);
    }

    return response()->json([
      'success' => true,
      'message' => 'Pending orders retrieved successfully.',
      'orders' => $ordersData
    ]);
  }

  //correct completed order
  public function completedOrders()
  {
    $orders = Orders::with(['user', 'product'])
      ->whereDoesntHave('deliveryRoutes')
      ->where('order_status', 'completed')
      ->orderBy('order_date', 'asc')
      ->paginate(10);
    $ordersData = $orders->items();
    // Use isEmpty()
    if (empty($ordersData)) {
      return response()->json([
        'success' => true,
        'message' => 'No completed orders available for assignment.',
        'orders' => [],

      ]);
    }

    return response()->json([
      'success' => true,
      'message' => 'Completed orders retrieved successfully.',
      'orders' => $ordersData
    ]);
  }

  //correct delivery order
  public function deliveryOrders()
  {
    $orders = Orders::with(['user', 'product'])
      ->whereDoesntHave('deliveryRoutes')
      ->where('order_status', 'delivery')
      ->orderBy('order_date', 'asc')
      ->paginate(10);
    $ordersData = $orders->items();
    // Use isEmpty()
    if (empty($ordersData)) {
      return response()->json([
        'success' => true,
        'message' => 'No completed orders available for assignment.',
        'orders' => [],

      ]);
    }

    return response()->json([
      'success' => true,
      'message' => 'Completed orders retrieved successfully.',
      'orders' => $ordersData
    ]);
  }
  public function ordersByStatus($status)
  {
    $orders = Orders::with(['user', 'product'])
      ->where('order_status', $status)
      ->orderBy('order_date', 'desc')
      ->paginate(10);

    $ordersData = $orders->items();
    if (empty($ordersData)) {
      return response()->json([
        'success' => true,
        'message' => "No {$status} orders found.",
        'orders' => [],

      ]);
    }


    return response()->json([
      'success' => true,
      'message' => "{$status} orders retrieved successfully.",
      'orders' => $ordersData,
      'status' => $status
    ]);
  }

  public function searchOrders(Request $request)
  {
    $search = $request->get('search');

    $orders = Orders::with(['user', 'product'])
      ->where(function ($query) use ($search) {
        $query->where('order_id', 'LIKE', "%{$search}%")
          ->orWhereHas('user', function ($q) use ($search) {
            $q->where('user_name', 'LIKE', "%{$search}%");
          })
          ->orWhereHas('product', function ($q) use ($search) {
            $q->where('product_name', 'LIKE', "%{$search}%");
          });
      })
      ->orderBy('order_date', 'desc')
      ->paginate(10);
    $ordersData = $orders->items();

    if (empty($ordersData)) {
      return response()->json([
        'success' => true,
        'message' => "No orders found for search term: '{$search}'",
        'orders' => [],

      ]);
    }

    return response()->json([
      'success' => true,
      'message' => "Search results for '{$search}'",
      'orders' => $ordersData,
      'search_term' => $search
    ]);
  }

  // public function showOrderDetail($orderId)
  // {
  //   $order = Orders::with([
  //     'user.township',
  //     'product',
  //     'deliveryRoutes.deliveryGroup',
  //     'deliveryRoutes.supervisor'
  //   ])
  //     ->where('order_id', $orderId)
  //     ->first();

  //   if (!$order) {
  //     return response()->json([
  //       'success' => false,
  //       'message' => 'Order not found.'
  //     ], 404);
  //   }

  //   return response()->json([
  //     'success' => true,
  //     'message' => 'Order details retrieved successfully.',
  //     'order' => $order
  //   ]);
  // }
  public function showOrderDetail($orderId)
  {
    $order = Orders::with([
      'user' => function ($query) {
        $query->withTrashed(); // ✅ Deleted users included
      },
      'product',
      'deliveryRoutes.deliveryGroup'
    ])
      ->where('order_id', $orderId)
      ->first();

    if (!$order) {
      return response()->json([
        'success' => false,
        'message' => 'Order not found.'
      ], 404);
    }

    return response()->json([
      'success' => true,
      'message' => 'Order details retrieved successfully.',
      'order' => $order
    ]);
  }
}