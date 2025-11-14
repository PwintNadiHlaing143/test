<?php


namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\DeliveryRoute;
use App\Models\DeliveryGroup;
use App\Models\Township;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class SupervisorOrderControllercopy extends Controller
{

  public function myAssignedOrders()
  {
    $supervisorId = auth()->id();

    $orders = Order::with(['user' => function ($query) {
      $query->withTrashed();
    }, 'product'])
      ->whereHas('deliveryRoutes', function ($query) use ($supervisorId) {
        $query->where('supervisor_id', $supervisorId);
      })
      ->orderBy('order_date', 'desc')
      ->paginate(10);

    $ordersData = $orders->items();

    if (empty($ordersData)) {
      return response()->json([
        'success' => true,
        'message' => 'No assigned orders available.',
        'orders' => [],
      ]);
    }

    return response()->json([
      'success' => true,
      'message' => 'Assigned orders retrieved successfully.',
      'orders' => $ordersData
    ]);
  }

  //correct supervisor look all user orders
  public function allOrders()
  {
    $orders = Order::with(['user' => function ($query) {
      $query->withTrashed();
    }, 'product', 'deliveryRoutes'])
      ->orderBy('order_date', 'desc')
      ->paginate(10);

    $ordersData = $orders->items();

    if (empty($ordersData)) {
      return response()->json([
        'success' => true,
        'message' => 'No orders available.',
        'orders' => [],
      ]);
    }

    return response()->json([
      'success' => true,
      'message' => 'All orders retrieved successfully.',
      'orders' => $ordersData
    ]);
  }

  public function acceptOrder(Request $request, $orderId)
  {
    try {
      $supervisorId = Auth::id();

      // Check if order exists and is pending
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
        'message' => 'Failed to accept order',
        'error' => $e->getMessage()
      ], 500);
    }
  }


  public function acceptMultipleOrders(Request $request)
  {
    try {
      $request->validate([
        'order_ids' => 'required|array',
        'order_ids.*' => 'exists:orders,order_id'
      ]);

      $supervisorId = Auth::id();
      $acceptedCount = 0;

      foreach ($request->order_ids as $orderId) {
        $order = Order::where('order_id', $orderId)
          ->where('order_status', 'pending')
          ->first();

        if ($order) {
          // ✅ ONLY update order status (NO delivery route)
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
        'message' => 'Failed to accept orders',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  // ✅ NEW: Get approved orders for route creation
  public function getApprovedOrders()
  {
    try {
      $orders = Order::with(['user', 'product'])
        ->where('order_status', 'approved')
        ->whereDoesntHave('deliveryRoutes') // Only orders without delivery routes
        ->orderBy('order_date', 'asc')
        ->paginate(10);

      $ordersData = $orders->items();

      if (empty($ordersData)) {
        return response()->json([
          'success' => true,
          'message' => 'No approved orders available for route assignment.',
          'orders' => [],
        ]);
      }

      return response()->json([
        'success' => true,
        'message' => 'Approved orders retrieved successfully.',
        'orders' => $ordersData,
        'total_approved' => $orders->total()
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve approved orders',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  // ✅ NEW: Create delivery route for approved orders
  public function createDeliveryRoute(Request $request)
  {
    try {
      $request->validate([
        'order_ids' => 'required|array',
        'order_ids.*' => 'exists:orders,order_id',
        'group_id' => 'required|exists:delivery_groups,group_id',
        'township_id' => 'required|exists:townships,township_id',
        'delivery_date' => 'required|date'
      ]);

      $supervisorId = Auth::id();
      $createdRoutes = [];

      foreach ($request->order_ids as $orderId) {
        // Check if order is approved and doesn't have delivery route
        $order = Order::where('order_id', $orderId)
          ->where('order_status', 'approved')
          ->whereDoesntHave('deliveryRoutes')
          ->first();

        if ($order) {
          // Create delivery route
          $deliveryRoute = DeliveryRoute::create([
            'group_id' => $request->group_id,
            'order_id' => $orderId,
            'supervisor_id' => $supervisorId,
            'township_id' => $request->township_id,
            'delivery_status' => 'assigned',
            'delivery_date' => $request->delivery_date,
          ]);

          // Update order status to assigned
          $order->update(['order_status' => 'assigned']);

          $createdRoutes[] = $deliveryRoute;
        }
      }

      return response()->json([
        'success' => true,
        'message' => 'Delivery routes created successfully',
        'data' => [
          'created_routes' => $createdRoutes,
          'total_created' => count($createdRoutes)
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to create delivery routes',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  // ✅ NEW: Get delivery groups and townships for assignment
  public function getAssignmentData()
  {
    try {
      $supervisorId = Auth::id();

      $deliveryGroups = DeliveryGroup::where('supervisor_id', $supervisorId)->get();
      $townships = Township::all();

      return response()->json([
        'success' => true,
        'message' => 'Assignment data retrieved successfully',
        'delivery_groups' => $deliveryGroups,
        'townships' => $townships
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to get assignment data',
        'error' => $e->getMessage()
      ], 500);
    }
  }


  // ✅ NEW: Get order statistics
  public function getOrderStats()
  {
    try {
      $stats = [
        'total_orders' => Order::count(),
        'pending_orders' => Order::where('order_status', 'pending')->count(),
        'approved_orders' => Order::where('order_status', 'approved')->count(),
        'completed_orders' => Order::where('order_status', 'completed')->count(),
        'cancelled_orders' => Order::where('order_status', 'cancelled')->count()
      ];

      return response()->json([
        'success' => true,
        'message' => 'Order statistics retrieved successfully',
        'stats' => $stats
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to get order statistics',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  // ========== YOUR EXISTING METHODS ==========


  //correct pending order
  public function pendingOrders()
  {
    $orders = Order::with(['user', 'product'])
      ->where('order_status', 'pending')
      ->orderBy('order_date', 'asc')
      ->paginate(10);

    $ordersData = $orders->items();

    if (empty($ordersData)) {
      return response()->json([
        'success' => true,
        'message' => 'No pending orders available.',
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
    $orders = Order::with(['user', 'product'])
      ->where('order_status', 'completed')
      ->orderBy('order_date', 'asc')
      ->paginate(10);

    $ordersData = $orders->items();

    if (empty($ordersData)) {
      return response()->json([
        'success' => true,
        'message' => 'No completed orders available.',
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
    $orders = Order::with(['user', 'product'])
      ->where('order_status', 'delivery')
      ->orderBy('order_date', 'asc')
      ->paginate(10);

    $ordersData = $orders->items();

    if (empty($ordersData)) {
      return response()->json([
        'success' => true,
        'message' => 'No delivery orders available.',
        'orders' => [],
      ]);
    }

    return response()->json([
      'success' => true,
      'message' => 'Delivery orders retrieved successfully.',
      'orders' => $ordersData
    ]);
  }

  public function ordersByStatus($status)
  {
    $orders = Order::with(['user', 'product'])
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

    $orders = Order::with(['user', 'product'])
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
        'message' => "No orders found for: '{$search}'",
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

  public function showOrderDetail($orderId)
  {
    $order = Order::with([
      'user' => function ($query) {
        $query->withTrashed();
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
