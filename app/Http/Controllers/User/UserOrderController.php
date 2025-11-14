<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class UserOrderController extends Controller
{
  public function getOrderLimitStatus(Request $request, $productId)
  {
    // Default values
    $isReturnable = false;
    $currentEmpty = 0;
    $maxEmptyBottles = 10;
    $orderedToday = 0;
    $maxPerDay = 5;
    $canAddMore = true;

    try {
      $user = $request->user();
      $product = Product::find($productId);

      if (!$product) {
        return response()->json([
          'success' => false,
          'message' => 'Product not found.',
          'data' => [
            'is_returnable' => $isReturnable,
            'current_empty_bottles' => $currentEmpty,
            'max_empty_bottles' => $maxEmptyBottles,
            'ordered_today' => $orderedToday,
            'max_per_day' => $maxPerDay,
            'can_add_more' => false,
          ]
        ], 404);
      }

      // ✅ Only treat 20-liter products as "returnable" and limited
      $isReturnable = stripos($product->product_name, '20') !== false
        && stripos($product->product_name, 'liter') !== false;

      if ($isReturnable) {
        // Get user's current empty 20L bottles
        $currentEmpty = $this->calculateCurrentBottles($user->user_id);

        // Count how many 20L bottles the user ordered today
        $orderedToday = Order::where('user_id', $user->user_id)
          ->whereHas('product', function ($q) {
            $q->where('product_name', 'like', '%20%')
              ->where('product_name', 'like', '%liter%');
          })
          ->whereDate('created_at', now()->toDateString())
          ->sum('order_quantity');

        // Apply limits for 20L only
        if ($currentEmpty >= $maxEmptyBottles || $orderedToday >= $maxPerDay) {
          $canAddMore = false;
        }
      } else {
        // ✅ Other products (1L, 5L) have no restriction
        $maxPerDay = null; // show as unlimited in response
        $canAddMore = true;
      }

      return response()->json([
        'success' => true,
        'data' => [
          'is_returnable' => $isReturnable,
          'current_empty_bottles' => $currentEmpty,
          'max_empty_bottles' => $maxEmptyBottles,
          'ordered_today' => $orderedToday,
          'max_per_day' => $maxPerDay,
          'can_add_more' => $canAddMore,
        ]
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'An unexpected error occurred while checking the order limit.',
        'error' => $e->getMessage(),
        'data' => [
          'is_returnable' => $isReturnable,
          'current_empty_bottles' => $currentEmpty,
          'max_empty_bottles' => $maxEmptyBottles,
          'ordered_today' => $orderedToday,
          'max_per_day' => $maxPerDay,
          'can_add_more' => $canAddMore,
        ]
      ], 500);
    }
  }



  public function createOrder(Request $request)
  {
    try {
      $user = $request->user();

      // Validate input
      $validator = Validator::make($request->all(), [
        'product_id' => 'required|exists:products,product_id',
        'order_quantity' => 'required|integer|min:1',
        'sold_price' => 'nullable|numeric|min:0',
        'notes' => 'nullable|string',
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Validation failed.',
          'errors' => $validator->errors(),
        ], 422);
      }

      $product = Product::find($request->product_id);
      $price = $request->sold_price ?? $product->unit_price;
      $quantity = $request->order_quantity;
      $total_amount = $price * $quantity;

      // Check if product is 20L
      $is20L = stripos($product->product_name, '20') !== false;

      // ✅ 20L daily limit (5 bottles/day)
      if ($is20L) {
        $today20LOrders = Order::where('user_id', $user->user_id)
          ->whereHas('product', fn($q) => $q->where('product_name', 'like', '%20%'))
          ->whereDate('created_at', now()->toDateString())
          ->sum('order_quantity');

        if (($today20LOrders + $quantity) > 5) {
          return response()->json([
            'success' => false,
            'message' => 'You can only order up to 5 bottles of 20L water per day.',
            'data' => [
              'ordered_today_20L' => $today20LOrders,
              'requested' => $quantity,
              'max_per_day' => 5
            ]
          ], 403);
        }
      }

      // ✅ Check unreturned bottles (20L only)
      if ($is20L) {
        $currentEmpty = $this->calculateCurrentBottles($user->user_id);
        if ($currentEmpty + $quantity > 10) {
          return response()->json([
            'success' => false,
            'message' => 'You cannot place a new 20L order because you already have 10 unreturned bottles.',
            'data' => [
              'current_empty' => $currentEmpty,
              'requested' => $quantity,
              'max_unreturned' => 10
            ]
          ], 403);
        }
      }

      // ✅ Create order
      $order = Order::create([
        'owner_id' => $product->owner_id ?? 1,
        'user_id' => $user->user_id,
        'product_id' => $product->product_id,
        'order_quantity' => $quantity,
        'sold_price' => $price,
        'total_amount' => $total_amount,
        'order_status' => 'pending',
        'notes' => $request->notes,
        'delivered_bottles' => 0,
        'remaining_bottles' => $is20L ? $quantity : 0,
      ]);

      return response()->json([
        'success' => true,
        'message' => 'Order created successfully.',
        'data' => $order
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'An unexpected error occurred while creating the order.',
        'error' => $e->getMessage(),
      ], 500);
    }
  }


  //owner look User order history
  public function userOrders(Request $request)
  {
    $user = $request->user();

    $orders = $user->orders()->with(['product', 'owner'])->orderBy('order_date', 'desc')->get();

    return response()->json([
      'success' => true,
      'data' => $orders
    ]);
  }

  // Pending orders
  // Pending, Approved, and Assigned orders
  public function pendingOrders(Request $request)
  {
    $user = $request->user();

    $orders = $user->orders()
      ->with(['product', 'owner'])
      ->whereIn('order_status', ['pending', 'approved', 'assigned']) // ✅ multiple statuses
      ->orderBy('order_date', 'desc')
      ->get();

    return response()->json([
      'success' => true,
      'message' => 'Orders retrieved successfully',
      'data' => $orders
    ]);
  }

  // User က completed orders ကြည့်မယ့် function
  public function completedOrders(Request $request)
  {
    $user = $request->user();

    $orders = $user->orders()
      ->with(['product'])
      ->where('order_status', 'completed') // ဒါမှမဟုတ် 'completed'
      ->orderBy('order_date', 'desc')
      ->get();

    return response()->json([
      'success' => true,
      'message' => 'Delivered orders retrieved successfully',
      'data' => $orders
    ]);
  }

  // User က သူ့ order status အားလုံးကြည့်မယ့် function
  public function myOrderStatus(Request $request)
  {
    $user = $request->user();

    $orders = $user->orders()
      ->with(['product'])
      ->select('order_id', 'product_id', 'quantity', 'order_status', 'order_date')
      ->orderBy('order_date', 'desc')
      ->get();

    return response()->json([
      'success' => true,
      'message' => 'Order status retrieved successfully',
      'orders' => $orders
    ]);
  }

  // correct canceled Order
  public function canceledOrders(Request $request)
  {
    $user = $request->user();

    $orders = $user->orders()
      ->with(['product', 'owner'])
      ->where('order_status', 'canceled')
      ->orderBy('order_date', 'desc')
      ->get();

    return response()->json([
      'success' => true,
      'data' => $orders
    ]);
  }

  //owner look user order
  // public function showOrder(Request $request, $order_id)
  // {
  //   $user = $request->user();

  //   $order = $user->orders()->with(['product', 'owner'])->where('order_id', $order_id)->first();

  //   if (!$order) {
  //     return response()->json([
  //       'success' => false,
  //       'message' => 'Order not found'
  //     ], 404);
  //   }

  //   return response()->json([
  //     'success' => true,
  //     'data' => $order
  //   ]);
  // }


  public function updateStatus(Request $request, $order_id)
  {
    $validator = Validator::make($request->all(), [
      'order_status' => 'required|string|in:pending,completed,canceled'
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation error',
        'errors' => $validator->errors()
      ], 422);
    }

    $order = Order::find($order_id);

    if (!$order) {
      return response()->json([
        'success' => false,
        'message' => 'Order not found'
      ], 404);
    }

    $order->order_status = $request->order_status;
    $order->save();

    return response()->json([
      'success' => true,
      'message' => 'Order status updated successfully',
      'data' => $order
    ]);
  }


  // User ရဲ့ Order History အားလုံးကြည့်မယ်
  public function getUserOrderHistory(Request $request)
  {
    try {
      $user = Auth::user();

      $orders = Order::with(['product', 'deliveryRoutes.township'])
        ->where('user_id', $user->user_id)
        ->orderBy('order_date', 'desc')
        ->paginate(10);

      return response()->json([
        'success' => true,
        'message' => 'Order history retrieved successfully',
        'data' => $orders
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve order history',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  // Specific Order Details ကြည့်မယ်
  public function getOrderDetails($orderId)
  {
    try {
      $user = Auth::user();

      $order = Order::with([
        'product',
        'deliveryRoutes.township',
        'deliveryRoutes.group.staff'
      ])
        ->where('order_id', $orderId)
        ->where('user_id', $user->user_id)
        ->first();

      if (!$order) {
        return response()->json([
          'success' => false,
          'message' => 'Order not found'
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Order details retrieved successfully',
        'data' => $order
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve order details',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  // Current Bottles Calculation (မင်းရဲ့ system logic အရ)
  public function getUserBottleStatistics()
  {
    try {
      $user = Auth::user();

      $statistics = [
        'total_orders' => Order::where('user_id', $user->user_id)->count(),
        'total_bottles_delivered' => Order::where('user_id', $user->user_id)->sum('order_quantity'),
        'total_empty_bottles_collected' => Order::where('user_id', $user->user_id)->sum('empty_collected'),
        'total_change_returned' => Order::where('user_id', $user->user_id)->sum('change_returned'),
        'current_bottles_with_customer' => $this->calculateCurrentBottles($user->user_id)
      ];

      return response()->json([
        'success' => true,
        'message' => 'Bottle statistics retrieved successfully',
        'data' => $statistics
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve statistics',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  private function calculateCurrentBottles($userId)
  {
    $totalDelivered = Order::where('user_id', $userId)
      ->where('order_status', 'pending')
      ->whereHas('product', fn($q) => $q->where('product_name', 'like', '%20%'))
      ->sum('order_quantity');

    $totalCollected = Order::where('user_id', $userId)
      ->sum('empty_collected');

    // minus ဖြစ်မလာအောင် ကာကွယ်မယ်
    return max(0, $totalDelivered - $totalCollected);
  }
}