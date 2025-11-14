<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
  use HasFactory;

  protected $table = 'products';

  protected $primaryKey = 'product_id';

  public $timestamps = true;

  protected $fillable = [
    'owner_id',
    'product_name',
    'unit_price',
    'current_stock',
    'description',
    'product_image', // Store image filename only
    'product_status'
  ];

  protected $casts = [
    'unit_price' => 'decimal:2',
    'current_stock' => 'integer',
    'product_status' => 'boolean',
    'created_at' => 'datetime',
    'updated_at' => 'datetime'
  ];

  protected $attributes = [
    'current_stock' => 0,
    'product_status' => true
  ];

  // Add image_url to appends
  protected $appends = [
    'image_url',
    'status_text',
    'stock_status',
    'inventory_value'
  ];

  // Relationship with Owner
  public function owner()
  {
    return $this->belongsTo(Owner::class, 'owner_id', 'owner_id');
  }

  // Relationship with Orders
  public function orders()
  {
    return $this->hasMany(Order::class, 'product_id', 'product_id');
  }

  // ==================== SIMPLE IMAGE METHODS ====================

  // Accessor for full image URL - SIMPLE VERSION
  public function getImageUrlAttribute()
  {
    if ($this->product_image) {
      return asset('storage/products/' . $this->product_image);
    }

    // Return default image if no image exists
    return asset('images/default-product.png');
  }

  // ==================== EXISTING METHODS ====================

  // Accessor for product status
  public function getStatusTextAttribute()
  {
    return $this->product_status ? 'Active' : 'Inactive';
  }

  // Accessor for stock status
  public function getStockStatusAttribute()
  {
    if ($this->current_stock == 0) {
      return 'Out of Stock';
    } elseif ($this->current_stock <= 10) {
      return 'Low Stock';
    } else {
      return 'In Stock';
    }
  }

  // Mutator for product name
  public function setProductNameAttribute($value)
  {
    $this->attributes['product_name'] = ucwords(strtolower($value));
  }

  // Scope for active products
  public function scopeActive($query)
  {
    return $query->where('product_status', true);
  }

  // Scope for in stock products
  public function scopeInStock($query)
  {
    return $query->where('current_stock', '>', 0);
  }

  // Scope for low stock products
  public function scopeLowStock($query)
  {
    return $query->where('current_stock', '>', 0)
      ->where('current_stock', '<=', 10);
  }

  // Scope for out of stock products
  public function scopeOutOfStock($query)
  {
    return $query->where('current_stock', 0);
  }

  // Scope for owner's products
  public function scopeByOwner($query, $ownerId)
  {
    return $query->where('owner_id', $ownerId);
  }

  // Check if product can be deleted
  public function canDelete()
  {
    return $this->orders()->count() === 0;
  }

  // Get total inventory value
  public function getInventoryValueAttribute()
  {
    return $this->unit_price * $this->current_stock;
  }
}
