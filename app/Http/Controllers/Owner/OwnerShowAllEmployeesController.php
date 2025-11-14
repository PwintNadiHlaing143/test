<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Supervisor;
use App\Models\User;
use App\Models\DeliveryStaff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerShowAllEmployeesController extends Controller
{
  public function showSupervisors(Request $request): JsonResponse
  {
    try {
      $ownerId = auth()->id();

      $query = Supervisor::where('owner_id', $ownerId);


      if ($request->has('search') && $request->search != '') {
        $query->where(function ($q) use ($request) {
          $q->where('supervisor_name', 'like', '%' . $request->search . '%')
            ->orWhere('supervisor_phone', 'like', '%' . $request->search . '%');
        });
      }

      if ($request->has('status') && $request->status != '') {
        $query->where('supervisor_status', $request->status);
      }

      $supervisors = $query->latest()->get();


      $stats = [
        'total_supervisors' => Supervisor::where('owner_id', $ownerId)->count(),
        'active_supervisors' => Supervisor::where('owner_id', $ownerId)
          ->where('supervisor_status', true)->count(),
        'inactive_supervisors' => Supervisor::where('owner_id', $ownerId)
          ->where('supervisor_status', false)->count(),
      ];

      return response()->json([
        'success' => true,
        'data' => $supervisors,
        'stats' => $stats,
        'message' => 'Supervisors retrieved successfully'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve supervisors',
        'error' => $e->getMessage()
      ], 500);
    }
  }
  public function showUsers(Request $request): JsonResponse
  {
    try {
      $query = User::with('township');

      // Search filter
      if ($request->has('search') && $request->search != '') {
        $query->where(function ($q) use ($request) {
          $q->where('user_name', 'like', '%' . $request->search . '%')
            ->orWhere('phone_number', 'like', '%' . $request->search . '%')
            ->orWhere('user_address', 'like', '%' . $request->search . '%');
        });
      }

      // Status filter
      if ($request->has('status') && $request->status != '') {
        if ($request->status === 'active') {
          $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
          $query->where('is_active', false);
        }
      }
      // Township filter
      if ($request->has('township_id') && $request->township_id != '') {
        $query->where('township_id', $request->township_id);
      }

      $users = $query->latest()->get();


      $stats = [
        'total_users' => User::count(),
        'active_users' => User::where('is_active', true)->count(),
        'inactive_users' => User::where('is_active', false)->count(),
      ];

      return response()->json([
        'success' => true,
        'data' => $users,
        'stats' => $stats,
        'message' => 'Users retrieved successfully'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve users',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function showDeliveryStaff(Request $request): JsonResponse
  {
    try {
      $query = DeliveryStaff::with('group');

      if ($request->has('search') && $request->search != '') {
        $query->where(function ($q) use ($request) {
          $q->where('staff_name', 'like', '%' . $request->search . '%')
            ->orWhere('staff_phone', 'like', '%' . $request->search . '%')
            ->orWhere('staff_address', 'like', '%' . $request->search . '%');
        });
      }


      if ($request->has('status') && $request->status != '') {
        $query->where('staff_status', $request->status);
      }

      // Group filter
      if ($request->has('group_id') && $request->group_id != '') {
        $query->where('group_id', $request->group_id);
      }
      $deliveryStaff = $query->latest()->get();

      $stats = [
        'total_delivery_staff' => DeliveryStaff::count(),
        'active_delivery_staff' => DeliveryStaff::where('staff_status', true)->count(),
        'inactive_delivery_staff' => DeliveryStaff::where('staff_status', false)->count(),
      ];

      return response()->json([
        'success' => true,
        'data' => $deliveryStaff,
        'stats' => $stats,
        'message' => 'Delivery staff retrieved successfully'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve delivery staff',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function getOverallStats(): JsonResponse
  {
    try {
      $ownerId = auth()->id();

      $stats = [
        'supervisors' => [
          'total' => Supervisor::where('owner_id', $ownerId)->count(),
          'active' => Supervisor::where('owner_id', $ownerId)->where('supervisor_status', true)->count(),
          'inactive' => Supervisor::where('owner_id', $ownerId)->where('supervisor_status', false)->count(),
        ],
        'users' => [
          'total' => User::count(),
          'active' => User::where('is_active', true)->count(),
          'inactive' => User::where('is_active', false)->count(),
        ],
        'delivery_staff' => [
          'total' => DeliveryStaff::count(),
          'active' => DeliveryStaff::where('staff_status', true)->count(),
          'inactive' => DeliveryStaff::where('staff_status', false)->count(),
        ],
        'total_all_employees' =>
        Supervisor::where('owner_id', $ownerId)->count() +
          User::count() +
          DeliveryStaff::count()
      ];

      return response()->json([
        'success' => true,
        'data' => $stats,
        'message' => 'Overall employee statistics retrieved successfully'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve statistics',
        'error' => $e->getMessage()
      ], 500);
    }
  }
}