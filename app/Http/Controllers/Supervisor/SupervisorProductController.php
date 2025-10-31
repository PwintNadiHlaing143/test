<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class SupervisorProductController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:api');
    $this->middleware('supervisor');
  }

  public function index(Request $request): JsonResponse
  {
    try {
      $supervisor = auth()->user();
      $ownerId = $supervisor->owner_id;

      $products = Products::where('owner_id', $ownerId)
        ->where('product_status', true)
        ->get();

      return response()->json([
        'success' => true,
        'data' => $products,
        'message' => 'Products retrieved successfully'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve products'
      ], 500);
    }
  }


  public function show(string $id): JsonResponse
  {
    try {
      $supervisor = auth()->user();
      $ownerId = $supervisor->owner_id;

      $product = Products::where('owner_id', $ownerId)
        ->where('product_id', $id)
        ->first();

      if (!$product) {
        return response()->json([
          'success' => false,
          'message' => 'Product not found'
        ], 404);
      }

      return response()->json([
        'success' => true,
        'data' => $product,
        'message' => 'Product retrieved successfully'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve product'
      ], 500);
    }
  }


  public function update(Request $request, string $id): JsonResponse
  {
    try {
      $supervisor = auth()->user();
      $ownerId = $supervisor->owner_id;

      $product = Products::where('owner_id', $ownerId)
        ->where('product_id', $id)
        ->first();

      if (!$product) {
        return response()->json([
          'success' => false,
          'message' => 'Product not found'
        ], 404);
      }


      $validated = $request->validate([
        'product_name' => 'sometimes|string|max:255',
        // 'unit_price' => 'sometimes|numeric|min:0', // ❌ PRICE CANNOT BE CHANGED
        'current_stock' => 'sometimes|integer|min:0',
        'description' => 'nullable|string',
        'product_image' => 'nullable|string|max:255',
        'product_status' => 'boolean'
      ]);


      if ($request->has('unit_price')) {
        return response()->json([
          'success' => false,
          'message' => 'Supervisors are not allowed to change product price'
        ], 403);
      }

      $product->update($validated);

      return response()->json([
        'success' => true,
        'data' => $product,
        'message' => 'Product updated successfully (except price)'
      ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
      return response()->json([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $e->errors()
      ], 422);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to update product',
        'error' => $e->getMessage()
      ], 500);
    }
  }


  public function updateStock(Request $request, string $id): JsonResponse
  {
    try {
      $supervisor = auth()->user();
      $ownerId = $supervisor->owner_id;

      $product = Products::where('owner_id', $ownerId)
        ->where('product_id', $id)
        ->first();

      if (!$product) {
        return response()->json([
          'success' => false,
          'message' => 'Product not found'
        ], 404);
      }

      $validated = $request->validate([
        'current_stock' => 'required|integer|min:0',
        'operation' => 'sometimes|in:add,set'
      ]);


      if ($request->has('unit_price')) {
        return response()->json([
          'success' => false,
          'message' => 'Supervisors cannot change product price'
        ], 403);
      }

      if ($request->operation === 'add') {
        $product->increment('current_stock', $validated['current_stock']);
      } else {
        $product->update(['current_stock' => $validated['current_stock']]);
      }

      return response()->json([
        'success' => true,
        'data' => $product->fresh(),
        'message' => 'Product stock updated successfully'
      ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
      return response()->json([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $e->errors()
      ], 422);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to update product stock',
        'error' => $e->getMessage()
      ], 500);
    }
  }
}
