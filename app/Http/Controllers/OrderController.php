<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\Products;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
  //User creates a new order
  public function createOrder(Request $request)
  {
    $user = $request->user();

    $validator = Validator::make($request->all(), [
      'product_id' => 'required|exists:products,product_id',
      'quantity' => 'required|integer|min:1',
      'sold_price' => 'nullable|numeric|min:0',
      'notes' => 'nullable|string',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation error',
        'errors' => $validator->errors()
      ], 422);
    }

    $product = Products::find($request->product_id);
    $price = $request->sold_price ?? $product->price;
    $total_amount = $price * $request->quantity;

    $order = Orders::create([
      'owner_id' => $product->owner_id,
      'product_id' => $product->product_id,
      'user_id' => $user->user_id,
      'order_quantity' => $request->quantity,
      'total_amount' => $total_amount,
      'sold_price' => $price,
      'order_status' => 'pending',
      'cash_collected' => 0,
      'change_returned' => 0,
      'empty_collected' => 0,
      'notes' => $request->notes
    ]);

    $order->load(['product', 'owner']);

    return response()->json([
      'success' => true,
      'message' => 'Order created successfully',
      'data' => $order
    ], 201);
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
  public function pendingOrders(Request $request)
  {
    $user = $request->user();

    $orders = $user->orders()
      ->with(['product', 'owner'])
      ->where('order_status', 'pending')
      ->orderBy('order_date', 'desc')
      ->get();

    return response()->json([
      'success' => true,
      'message' => 'Pending orders retrieved successfully',
      'data' => $orders
    ]);
  }

  // Completed orders
  public function completedOrders(Request $request)
  {
    $user = $request->user();

    $orders = $user->orders()
      ->with(['product', 'owner'])
      ->where('order_status', 'completed')
      ->orderBy('order_date', 'desc')
      ->get();

    return response()->json([
      'success' => true,
      'message' => 'Completed orders retrieved successfully',
      'data' => $orders
    ]);
  }


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

    $order = Orders::find($order_id);

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
}
