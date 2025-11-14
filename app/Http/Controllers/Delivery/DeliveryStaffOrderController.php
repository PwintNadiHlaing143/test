<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRoute;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DeliveryStaffOrderController extends Controller
{
  //correct look assign order by supervisor 
  public function reorderForUser(Request $request)
  {
    try {
      DB::beginTransaction();

      $request->validate([
        'user_id' => 'required|integer|exists:users,user_id',
        'product_id' => 'required|integer|exists:products,product_id',
        'order_quantity' => 'required|integer|min:1',
        'delivery_date' => 'required|date|after:today',
        'township_id' => 'required|integer|exists:townships,township_id',
        'sold_price' => 'nullable|numeric|min:0',
        'notes' => 'sometimes|string|max:500',
      ]);

      $staff = Auth::user();

      $deliveryGroup = \App\Models\DeliveryGroup::where('group_id', $staff->group_id)->first();

      if (!$deliveryGroup || !$deliveryGroup->supervisor_id) {
        DB::rollBack();
        return response()->json([
          'success' => false,
          'message' => 'No supervisor assigned to your delivery group',
        ], 400);
      }

      $supervisorId = $deliveryGroup->supervisor_id;

      $product = Product::find($request->product_id);
      if (!$product) {
        DB::rollBack();
        return response()->json([
          'success' => false,
          'message' => 'Product not found'
        ], 404);
      }

      if ($product->current_stock < $request->order_quantity) {
        DB::rollBack();
        return response()->json([
          'success' => false,
          'message' => 'Insufficient stock. Available: ' . $product->current_stock
        ], 400);
      }

      // Use the same price calculation logic as createOrder
      $price = $request->sold_price ?? $product->unit_price;
      $quantity = $request->order_quantity;
      $total_amount = $price * $quantity;

      // Check if product is 20L
      $is20L = stripos($product->product_name, '20') !== false;

      // ✅ 20L daily limit (5 bottles/day) - same as createOrder
      if ($is20L) {
        $today20LOrders = Order::where('user_id', $request->user_id)
          ->whereHas('product', fn($q) => $q->where('product_name', 'like', '%20%'))
          ->whereDate('created_at', now()->toDateString())
          ->sum('order_quantity');

        if (($today20LOrders + $quantity) > 5) {
          DB::rollBack();
          return response()->json([
            'success' => false,
            'message' => 'User can only order up to 5 bottles of 20L water per day.',
            'data' => [
              'ordered_today_20L' => $today20LOrders,
              'requested' => $quantity,
              'max_per_day' => 5
            ]
          ], 403);
        }
      }

      // ✅ Check unreturned bottles (20L only) - same as createOrder
      if ($is20L) {
        $currentEmpty = $this->calculateCurrentBottles($request->user_id);
        if ($currentEmpty + $quantity > 10) {
          DB::rollBack();
          return response()->json([
            'success' => false,
            'message' => 'User cannot place a new 20L order because they already have 10 unreturned bottles.',
            'data' => [
              'current_empty' => $currentEmpty,
              'requested' => $quantity,
              'max_unreturned' => 10
            ]
          ], 403);
        }
      }

      // Create order with same column structure as createOrder
      $order = Order::create([
        'owner_id' => $product->owner_id ?? 1,
        'user_id' => $request->user_id,
        'product_id' => $request->product_id,
        'order_quantity' => $quantity,
        'sold_price' => $price,
        'total_amount' => $total_amount,
        'order_status' => 'pending',
        'notes' => $request->notes ?? null,
        'delivered_bottles' => 0,
        'remaining_bottles' => $is20L ? $quantity : 0,
        'order_date' => now(), // Keep your original field
        'unit_price' => $product->unit_price, // Keep your original field
      ]);

      // Keep your delivery route creation logic
      $deliveryRoute = DeliveryRoute::create([
        'group_id' => $staff->group_id,
        'order_id' => $order->order_id,
        'supervisor_id' => $supervisorId,
        'township_id' => $request->township_id,
        'delivery_status' => 'scheduled',
        'delivery_date' => $request->delivery_date
      ]);

      $product->decrement('current_stock', $request->order_quantity);

      DB::commit();

      $order->load(['product', 'user']);

      return response()->json([
        'success' => true,
        'message' => 'Order created successfully',
        'data' => [
          'order' => $order,
          'delivery_route' => $deliveryRoute
        ]
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Failed to create order',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function viewAssignedOrders(Request $request)
  {
    try {
      $staff = Auth::user();

      $routes = DeliveryRoute::with(['order.product', 'order.user', 'township'])
        ->where('group_id', $staff->group_id)
        ->where('delivery_status', '=', 'assigned')
        ->orderBy('delivery_date', 'asc')
        ->paginate(10);

      $routesData = $routes->items();

      if (empty($routesData)) {
        return response()->json([
          'success' => true,
          'message' => 'No assigned orders found',
          'data' => [],
          'total_orders' => 0
        ]);
      }

      return response()->json([
        'success' => true,
        'message' => 'Assigned orders retrieved successfully',
        'data' => $routesData,
        'total_orders' => $routes->total()
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve assigned orders',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  //delivery staff update this status
  public function updateDeliveryStatus(Request $request, $routeId, $orderId)
  {
    DB::beginTransaction();

    try {
      $request->validate([
        'delivery_status' => 'required|in:completed',
        'delivered_bottles' => 'required|integer|min:0',
        'remaining_bottles' => 'required|integer|min:0',
        'cash_collected' => 'required|numeric|min:0',
        'empty_collected' => 'required|integer|min:0',
        'change_returned' => 'required|integer|min:0',
      ]);

      $order = Order::findOrFail($orderId);
      $user = $order->user;

      $userChangeReturn = floatval($user->change_return);
      $orderAmount = floatval($order->total_amount);
      $cashCollected = floatval($request->cash_collected);
      $changeReturnedFromRequest = floatval($request->change_returned);

      // 🔹 FIXED LOGIC: User change_return ကို accumulate လုပ်မယ်
      if ($userChangeReturn > 0) {
        // User မှာ change_return ရှိရင် အရင်သုံးမယ်
        if ($userChangeReturn >= $orderAmount) {
          // Change return က order amount ထက်များရင်
          $user->change_return = $userChangeReturn - $orderAmount;
          $orderAmount = 0;
          $cashCollected = 0;
          $finalChangeReturned = 0;
        } else {
          // Change return က order amount ထက်နည်းရင်
          $orderAmount -= $userChangeReturn;
          $user->change_return = 0;

          // ဒီ order အတွက် change_returned ကို accumulate လုပ်မယ်
          $user->change_return += $changeReturnedFromRequest;
          $finalChangeReturned = $changeReturnedFromRequest;
        }
      } else {
        // User မှာ change_return မရှိရင်
        $user->change_return = $changeReturnedFromRequest;
        $finalChangeReturned = $changeReturnedFromRequest;
      }

      // update order
      $order->update([
        'order_status' => $request->delivery_status,
        'delivered_bottles' => $request->delivered_bottles,
        'remaining_bottles' => $request->remaining_bottles,
        'cash_collected' => $cashCollected,
        'empty_collected' => $request->empty_collected,
        'change_returned' => $finalChangeReturned,
        'total_amount' => $orderAmount,
      ]);

      // Also update delivery route status
      DeliveryRoute::where('route_id', $routeId)
        ->where('order_id', $orderId)
        ->update(['delivery_status' => 'completed']);

      // update user
      $user->save();

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Order and user change_return updated successfully',
        'order' => $order->load('user'),
        'user' => $user
      ]);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => $e->getMessage(),
      ], 500);
    }
  }

  //delivery staff look order detail
  public function getOrderDetails($routeId)
  {
    try {
      $staff = Auth::user();

      $route = DeliveryRoute::with([
        'order.product',
        'order.user',
        'township',
        'group'
      ])
        ->where('route_id', $routeId)
        ->where('group_id', $staff->group_id)
        ->first();

      if (!$route) {
        return response()->json([
          'success' => false,
          'message' => 'Order not found or not assigned to your group'
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Order details retrieved successfully',
        'data' => $route
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve order details',
        'error' => $e->getMessage()
      ], 500);
    }
  }



  //delivery staff look his order history
  public function viewCompletedOrders(Request $request)
  {
    try {
      $staff = Auth::user();

      $routes = DeliveryRoute::with(['order.product', 'order.user', 'township'])
        ->where('group_id', $staff->group_id)
        ->where('delivery_status', 'completed')
        ->orderBy('delivery_date', 'desc')
        ->paginate(10);

      $routesData = $routes->items();

      if (empty($routesData)) {
        return response()->json([
          'success' => true,
          'message' => 'No completed orders found',
          'data' => [],
          'total_completed' => 0
        ]);
      }

      return response()->json([
        'success' => true,
        'message' => 'Completed orders retrieved successfully',
        'data' => $routesData,
        'total_completed' => $routes->total(),
        'pagination' => [
          'current_page' => $routes->currentPage(),
          'last_page' => $routes->lastPage(),
          'per_page' => $routes->perPage(),
          'total' => $routes->total()
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve completed orders',
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

    return $totalDelivered - $totalCollected;
  }
}