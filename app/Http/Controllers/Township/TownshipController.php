<?php

namespace App\Http\Controllers\Township;

use App\Http\Controllers\Controller;

use App\Models\Township;
use Illuminate\Http\Request;

class TownshipController extends Controller
{

  public function index()
  {
    try {
      $townships = Township::select('township_id', 'township_name', 'created_at', 'updated_at')
        ->orderBy('township_name', 'asc')
        ->get();

      return response()->json([
        'success' => true,
        'message' => 'Townships retrieved successfully',
        'data' => [
          'townships' => $townships,
          'total' => $townships->count()
        ]
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve townships',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  public function show($id)
  {
    try {
      $township = Township::select('township_id', 'township_name', 'created_at', 'updated_at')
        ->find($id);

      if (!$township) {
        return response()->json([
          'success' => false,
          'message' => 'Township not found'
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Township retrieved successfully',
        'data' => [
          'township' => $township
        ]
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve township',
        'error' => $e->getMessage()
      ], 500);
    }
  }


  public function search(Request $request)
  {
    try {
      $validator = \Validator::make($request->all(), [
        'township_name' => 'required|string|min:2'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Validation error',
          'errors' => $validator->errors()
        ], 422);
      }

      $townships = Township::select('township_id', 'township_name', 'created_at', 'updated_at')
        ->where('township_name', 'like', '%' . $request->name . '%')
        ->orderBy('township_name', 'asc')
        ->get();

      return response()->json([
        'success' => true,
        'message' => 'Townships search completed',
        'data' => [
          'townships' => $townships,
          'total' => $townships->count(),
          'search_term' => $request->name
        ]
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Search failed',
        'error' => $e->getMessage()
      ], 500);
    }
  }


  public function withUserCount()
  {
    try {
      $townships = Township::withCount('users')
        ->select('township_id', 'township_name', 'created_at', 'updated_at')
        ->orderBy('township_name', 'asc')
        ->get();

      return response()->json([
        'success' => true,
        'message' => 'Townships with user count retrieved successfully',
        'data' => [
          'townships' => $townships,
          'total' => $townships->count()
        ]
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve townships',
        'error' => $e->getMessage()
      ], 500);
    }
  }
}