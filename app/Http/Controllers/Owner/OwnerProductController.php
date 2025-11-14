<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OwnerProductController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:api');
  }

  public function index(Request $request): JsonResponse
  {
    try {
      $ownerId = auth()->id();

      $query = Product::where('owner_id', $ownerId);

      // Search filter
      if ($request->has('search')) {
        $query->where('product_name', 'like', '%' . $request->search . '%');
      }

      // Status filter
      if ($request->has('status')) {
        $query->where('product_status', $request->status);
      }

      // Stock filter
      if ($request->has('stock_status')) {
        if ($request->stock_status === 'out_of_stock') {
          $query->where('current_stock', 0);
        } elseif ($request->stock_status === 'low_stock') {
          $query->where('current_stock', '>', 0)
            ->where('current_stock', '<=', 10);
        } elseif ($request->stock_status === 'in_stock') {
          $query->where('current_stock', '>', 10);
        }
      }

      // Pagination
      $perPage = $request->get('per_page', 15);
      $products = $query->latest()->paginate($perPage);

      // Get summary statistics
      $stats = [
        'total_products' => Product::where('owner_id', $ownerId)->count(),
        'active_products' => Product::where('owner_id', $ownerId)->where('product_status', true)->count(),
        'out_of_stock' => Product::where('owner_id', $ownerId)->where('current_stock', 0)->count(),
        'low_stock' => Product::where('owner_id', $ownerId)
          ->where('current_stock', '>', 0)
          ->where('current_stock', '<=', 10)
          ->count(),
        'total_inventory_value' => Product::where('owner_id', $ownerId)
          ->sum(DB::raw('unit_price * current_stock'))
      ];

      return response()->json([
        'success' => true,
        'data' => $products,
        'stats' => $stats,
        'message' => 'Products retrieved successfully'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to retrieve products',
        'error' => $e->getMessage()
      ], 500);
    }
  }


  public function store(Request $request): JsonResponse
  {
    try {
      $validated = $request->validate([
        'product_name' => 'required|string|max:255|unique:products,product_name,NULL,product_id,owner_id,' . auth()->id(),
        'unit_price' => 'required|numeric|min:0',
        'current_stock' => 'required|integer|min:0',
        'description' => 'nullable|string',
        'product_image' => 'nullable|string|max:255',
        'product_status' => 'boolean'
      ]);

      $product = Product::create([
        ...$validated,
        'owner_id' => auth()->id()
      ]);

      return response()->json([
        'success' => true,
        'data' => $product,
        'message' => 'Product created successfully'
      ], 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
      return response()->json([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $e->errors()
      ], 422);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to create product',
        'error' => $e->getMessage()
      ], 500);
    }
  }


  public function show(string $id): JsonResponse
  {
    try {
      $ownerId = auth()->id();
      $product = Product::where('owner_id', $ownerId)
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
        'message' => 'Failed to retrieve product',
        'error' => $e->getMessage()
      ], 500);
    }
  }


  public function update(Request $request, string $id): JsonResponse
  {
    try {
      $ownerId = auth()->id();
      $product = Product::where('owner_id', $ownerId)
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
        'unit_price' => 'sometimes|numeric|min:0',
        'current_stock' => 'sometimes|integer|min:0',
        'description' => 'nullable|string',
        'product_image' => 'nullable|string|max:255',
        'product_status' => 'boolean'
      ]);

      $product->update($validated);

      return response()->json([
        'success' => true,
        'data' => $product,
        'message' => 'Product updated successfully'
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


  public function destroy(string $id): JsonResponse
  {
    try {
      $ownerId = auth()->id();
      $product = Product::where('owner_id', $ownerId)
        ->where('product_id', $id)
        ->first();

      if (!$product) {
        return response()->json([
          'success' => false,
          'message' => 'Product not found'
        ], 404);
      }

      // Check if product has existing orders
      $hasOrders = DB::table('orders')
        ->where('product_id', $id)
        ->exists();

      if ($hasOrders) {
        return response()->json([
          'success' => false,
          'message' => 'Cannot delete product with existing orders. You can deactivate it instead.'
        ], 422);
      }

      $product->delete();

      return response()->json([
        'success' => true,
        'message' => 'Product deleted successfully'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to delete product',
        'error' => $e->getMessage()
      ], 500);
    }
  }


  public function updateStock(Request $request, string $id): JsonResponse
  {
    try {
      $ownerId = auth()->id();
      $product = Product::where('owner_id', $ownerId)
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
        'operation' => 'sometimes|in:add,set' // add: add to current, set: set new value
      ]);

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

  public function updatePrice(Request $request, string $id): JsonResponse
  {
    try {
      $ownerId = auth()->id();
      $product = Product::where('owner_id', $ownerId)
        ->where('product_id', $id)
        ->first();

      if (!$product) {
        return response()->json([
          'success' => false,
          'message' => 'Product not found'
        ], 404);
      }

      $validated = $request->validate([
        'unit_price' => 'required|numeric|min:0'
      ]);

      $product->update(['unit_price' => $validated['unit_price']]);

      return response()->json([
        'success' => true,
        'data' => $product,
        'message' => 'Product price updated successfully'
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
        'message' => 'Failed to update product price',
        'error' => $e->getMessage()
      ], 500);
    }
  }
}