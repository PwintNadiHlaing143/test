<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\DeliveryGroup;
use App\Models\DeliveryStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryGroupController extends Controller
{
  public function index()
  {
    $supervisorId = Auth::id();

    $groups = DeliveryGroup::with(['deliveryStaff', 'deliveryRoutes.order.product'])
      ->where('supervisor_id', $supervisorId)
      ->get()
      ->map(function ($group) {

        if ($group->deliveryStaff->isEmpty()) {
          $group->delivery_staff = [
            'message' => 'No staff assigned to this group'
          ];
        }

        if ($group->deliveryRoutes->isEmpty()) {
          $group->delivery_routes = [
            'message' => 'No delivery routes assigned to this group'
          ];
        }

        return $group;
      });

    return response()->json([
      'success' => true,
      'message' => 'Delivery groups retrieved successfully',
      'data' => $groups
    ]);
  }
  public function show($groupId)
  {
    $group = DeliveryGroup::with(['deliveryStaff', 'deliveryRoutes.order.product'])
      ->where('group_id', $groupId)
      ->where('supervisor_id', Auth::id())
      ->firstOrFail();

    $availableStaff = DeliveryStaff::where(function ($query) use ($groupId) {
      $query->whereNull('group_id')->orWhere('group_id', $groupId);
    })->get();

    return response()->json([
      'success' => true,
      'data' => [
        'group' => $group,
        'available_staff' => $availableStaff
      ]
    ]);
  }


  // Assign staff to delivery group
  public function assignStaff(Request $request, $groupId)
  {
    $request->validate([
      'staff_ids' => 'required|array',
      'staff_ids.*' => 'exists:delivery_staff,staff_id'
    ]);

    $deliveryGroup = DeliveryGroup::where('group_id', $groupId)
      ->where('supervisor_id', Auth::id())
      ->firstOrFail();

    // Update staff group assignment
    DeliveryStaff::whereIn('staff_id', $request->staff_ids)
      ->update(['group_id' => $groupId]);

    return response()->json([
      'success' => true,
      'message' => 'Staff assigned to group successfully'
    ]);
  }

  // Remove staff from delivery group
  public function removeStaff($groupId, $staffId)
  {
    $deliveryGroup = DeliveryGroup::where('group_id', $groupId)
      ->where('supervisor_id', Auth::id())
      ->firstOrFail();

    DeliveryStaff::where('staff_id', $staffId)
      ->where('group_id', $groupId)
      ->update(['group_id' => null]);

    return response()->json([
      'success' => true,
      'message' => 'Staff removed from group successfully'
    ]);
  }

  // Get group performance statistics
  public function getGroupStats($groupId)
  {
    $deliveryGroup = DeliveryGroup::where('group_id', $groupId)
      ->where('supervisor_id', Auth::id())
      ->firstOrFail();

    $stats = [
      'total_assignments' => $deliveryGroup->deliveryRoutes()->count(),
      'completed_deliveries' => $deliveryGroup->deliveryRoutes()->where('delivery_status', 'completed')->count(),
      'pending_deliveries' => $deliveryGroup->deliveryRoutes()->where('delivery_status', 'pending')->count(),
      'in_progress_deliveries' => $deliveryGroup->deliveryRoutes()->where('delivery_status', 'in_progress')->count(),
      'staff_count' => $deliveryGroup->deliveryStaff()->count(),
    ];

    return response()->json($stats);
  }
}