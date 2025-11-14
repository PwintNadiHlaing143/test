<?php


namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class UserProductController extends Controller
{

  public function getAllProducts(Request $request)
  {
    $products = Product::with('owner')
      ->active() // Only remove inStock() to show out of stock products too
      ->orderBy('product_id')
      ->paginate(12);
    $productsData = $products->items();

    return response()->json([
      'success' => true,
      'message' => 'Products retrieved successfully',
      'products' => $productsData
    ]);
  }

  public function getProduct($productId)
  {
    $product = Product::with('owner')
      ->active()
      ->find($productId);

    if (!$product) {
      return response()->json([
        'success' => false,
        'message' => 'Product not found or not available'
      ], 404);
    }

    return response()->json([
      'success' => true,
      'message' => 'Product details retrieved successfully',
      'product' => $product
    ]);
  }


  public function searchProducts(Request $request)
  {
    $request->validate([
      'search' => 'nullable|string|max:100'
    ]);

    $search = $request->get('search');

    $products = Product::with('owner')
      ->active()
      ->inStock()
      ->when($search, function ($query) use ($search) {
        return $query->where('product_name', 'like', "%{$search}%");
      })
      ->orderBy('product_name')
      ->paginate(12);

    return response()->json([
      'success' => true,
      'message' => $search ? "Search results for '{$search}'" : 'All products',
      'search_term' => $search,
      'products' => $products
    ]);
  }


  public function getProductsByStock($stockStatus)
  {
    $validStatuses = ['in-stock', 'low-stock', 'out-of-stock'];

    if (!in_array($stockStatus, $validStatuses)) {
      return response()->json([
        'success' => false,
        'message' => 'Invalid stock status'
      ], 400);
    }

    $products = Product::with('owner')
      ->active()
      ->when($stockStatus === 'in-stock', function ($query) {
        return $query->where('current_stock', '>', 10);
      })
      ->when($stockStatus === 'low-stock', function ($query) {
        return $query->whereBetween('current_stock', [1, 10]);
      })
      ->when($stockStatus === 'out-of-stock', function ($query) {
        return $query->where('current_stock', 0);
      })
      ->orderBy('product_name')
      ->paginate(12);

    $message = match ($stockStatus) {
      'in-stock' => 'In stock products retrieved successfully',
      'low-stock' => 'Low stock products retrieved successfully',
      'out-of-stock' => 'Out of stock products retrieved successfully',
    };

    return response()->json([
      'success' => true,
      'message' => $message,
      'stock_status' => $stockStatus,
      'products' => $products
    ]);
  }

  public function getFeaturedProducts()
  {
    $products = Product::with('owner')
      ->active()
      ->inStock()
      ->orderBy('current_stock', 'desc')
      ->limit(8)
      ->get();

    return response()->json([
      'success' => true,
      'message' => 'Featured products retrieved successfully',
      'products' => $products
    ]);
  }
}
