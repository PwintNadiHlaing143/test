<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRoute;
use App\Models\Order;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryRouteController extends Controller
{
  //Get all delivery routes
  public function getAllRoutes()
  {
    $supervisorId = Auth::id();

    $routes = DeliveryRoute::with(['order.product', 'deliveryGroup', 'township'])
      ->where('supervisor_id', $supervisorId)
      ->latest()
      ->paginate(20);

    $routesData = $routes->items();

    if (empty($routesData)) {
      return response()->json([
        'success' => true,
        'message' => 'No delivery routes found',
        'routes' => []
      ]);
    }

    return response()->json([
      'success' => true,
      'message' => 'Delivery routes retrieved successfully',
      'routes' => $routesData
    ]);
  }

  //Create new delivery route
  public function createRoute(Request $request)
  {
    $request->validate([
      'order_ids' => 'required|array',
      'order_ids.*' => 'exists:orders,order_id',
      'group_id' => 'required|exists:delivery_group,group_id',
      'township_id' => 'required|exists:townships,township_id',
      'delivery_date' => 'required|date',
    ]);

    $supervisorId = Auth::id();
    $createdRoutes = [];

    foreach ($request->order_ids as $orderId) {
      // Check if order is approved
      $order = Order::where('order_id', $orderId)
        ->where('order_status', 'approved')
        ->first();

      if ($order) {
        $deliveryRoute = DeliveryRoute::create([
          'group_id' => $request->group_id,
          'order_id' => $orderId,
          'supervisor_id' => $supervisorId,
          'township_id' => $request->township_id,
          'delivery_status' => 'assigned',
          'delivery_date' => $request->delivery_date,
        ]);

        $order->update(['order_status' => 'assigned']);
        $createdRoutes[] = $deliveryRoute;
      }
    }

    return response()->json([
      'success' => true,
      'message' => 'Delivery route created successfully',
      'routes' => $createdRoutes
    ]);
  }

  //Get route details
  public function getRouteDetails($routeId)
  {
    $route = DeliveryRoute::with(['order.product', 'deliveryGroup.deliveryStaff', 'township'])
      ->where('route_id', $routeId)
      ->where('supervisor_id', Auth::id())
      ->first();

    if (!$route) {
      return response()->json([
        'success' => false,
        'message' => 'Route not found'
      ], 404);
    }

    return response()->json([
      'success' => true,
      'message' => 'Route details retrieved successfully',
      'route' => $route
    ]);
  }

  //Update route status
  public function updateRouteStatus(Request $request, $routeId)
  {
    $request->validate([
      'delivery_status' => 'required|in:assigned,in_progress,completed,cancelled'
    ]);

    $route = DeliveryRoute::where('route_id', $routeId)
      ->where('supervisor_id', Auth::id())
      ->first();

    if (!$route) {
      return response()->json([
        'success' => false,
        'message' => 'Route not found'
      ], 404);
    }

    $route->update(['delivery_status' => $request->delivery_status]);

    return response()->json([
      'success' => true,
      'message' => 'Route status updated successfully',
      'route' => $route
    ]);
  }

  //Delete route
  public function deleteRoute($routeId)
  {
    $route = DeliveryRoute::where('route_id', $routeId)
      ->where('supervisor_id', Auth::id())
      ->first();

    if (!$route) {
      return response()->json([
        'success' => false,
        'message' => 'Route not found'
      ], 404);
    }

    // Change order status back to approved
    Order::where('order_id', $route->order_id)->update([
      'order_status' => 'approved'
    ]);

    $route->delete();

    return response()->json([
      'success' => true,
      'message' => 'Route deleted successfully'
    ]);
  }

  //Get route statistics
  public function getRouteStats()
  {
    $supervisorId = Auth::id();

    $stats = DeliveryRoute::where('supervisor_id', $supervisorId)
      ->selectRaw('COUNT(*) as total_routes')
      ->selectRaw('SUM(CASE WHEN delivery_status = "assigned" THEN 1 ELSE 0 END) as assigned_routes')
      ->selectRaw('SUM(CASE WHEN delivery_status = "in_progress" THEN 1 ELSE 0 END) as in_progress_routes')
      ->selectRaw('SUM(CASE WHEN delivery_status = "completed" THEN 1 ELSE 0 END) as completed_routes')
      ->first();

    return response()->json([
      'success' => true,
      'message' => 'Route statistics retrieved successfully',
      'stats' => $stats
    ]);
  }
}
