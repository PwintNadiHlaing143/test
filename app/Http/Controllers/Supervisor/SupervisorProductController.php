<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SupervisorProductController extends Controller
{

  public function index(Request $request): JsonResponse
  {
    try {
      $supervisor = auth()->user();
      $ownerId = $supervisor->owner_id;

      $products = Product::where('owner_id', $ownerId)
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
        'message' => 'Failed to retrieve product'
      ], 500);
    }
  }

  public function update(Request $request, string $id): JsonResponse
  {
    try {
      $supervisor = auth()->user();
      $ownerId = $supervisor->owner_id;

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
        'current_stock' => 'sometimes|integer|min:0',
        'description' => 'nullable|string',
        'product_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Changed to image validation
        'product_status' => 'boolean'
      ]);

      if ($request->has('unit_price')) {
        return response()->json([
          'success' => false,
          'message' => 'Supervisors are not allowed to change product price'
        ], 403);
      }

      $updateData = $validated;

      // Handle image upload
      if ($request->hasFile('product_image')) {
        // Delete old image if exists
        if ($product->product_image) {
          Storage::delete('public/products/' . $product->product_image);
        }

        $image = $request->file('product_image');
        $imageName = 'product_' . $product->product_id . '_' . time() . '.' . $image->getClientOriginalExtension();

        // Store image
        $image->storeAs('public/products', $imageName);

        // Save filename to database
        $updateData['product_image'] = $imageName;
      }

      $product->update($updateData);

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

  // New method to upload image only
  public function uploadImage(Request $request, string $id): JsonResponse
  {
    try {
      $supervisor = auth()->user();
      $ownerId = $supervisor->owner_id;

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
        'product_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120'
      ]);

      // Delete old image if exists
      if ($product->product_image) {
        Storage::delete('public/products/' . $product->product_image);
      }

      $image = $request->file('product_image');
      $imageName = 'product_' . $product->product_id . '_' . time() . '.' . $image->getClientOriginalExtension();

      // Store image
      $image->storeAs('public/products', $imageName);

      // Update product with new image filename
      $product->update(['product_image' => $imageName]);

      return response()->json([
        'success' => true,
        'data' => $product->fresh(),
        'message' => 'Product image uploaded successfully'
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
        'message' => 'Failed to upload product image',
        'error' => $e->getMessage()
      ], 500);
    }
  }

  // New method to delete image
  public function deleteImage(string $id): JsonResponse
  {
    try {
      $supervisor = auth()->user();
      $ownerId = $supervisor->owner_id;

      $product = Product::where('owner_id', $ownerId)
        ->where('product_id', $id)
        ->first();

      if (!$product) {
        return response()->json([
          'success' => false,
          'message' => 'Product not found'
        ], 404);
      }

      if (!$product->product_image) {
        return response()->json([
          'success' => false,
          'message' => 'Product does not have an image'
        ], 404);
      }

      // Delete image from storage
      Storage::delete('public/products/' . $product->product_image);

      // Remove image filename from database
      $product->update(['product_image' => null]);

      return response()->json([
        'success' => true,
        'data' => $product->fresh(),
        'message' => 'Product image deleted successfully'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to delete product image',
        'error' => $e->getMessage()
      ], 500);
    }
  }
}
