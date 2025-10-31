<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products;
use App\Models\User;
use App\Models\Owner;
use App\Models\DeliveryRoute;

class Orders extends Model
{
  use HasFactory;

  // Table name (optional if table name is 'orders')
  protected $table = 'orders';

  // Primary key
  protected $primaryKey = 'order_id';

  // Fillable fields
  protected $fillable = [
    'owner_id',
    'product_id',
    'user_id',
    'order_quantity',
    'total_amount',
    'order_date',
    'order_status',
    'sold_price',
    'cash_collected',
    'change_returned',
    'empty_collected',
    'notes'
  ];

  // Relationships

  // User who made the order
  public function user()
  {
    return $this->belongsTo(User::class, 'user_id', 'user_id');
  }
  public function deliveryRoutes()
  {
    return $this->hasMany(DeliveryRoute::class, 'order_id', 'order_id');
  }

  // Product being ordered
  public function product()
  {
    return $this->belongsTo(Products::class, 'product_id', 'product_id');
  }

  // Owner (seller) of the product
  public function owner()
  {
    return $this->belongsTo(Owner::class, 'owner_id', 'owner_id');
  }

  // Township where the order is delivered
  public function township()
  {
    return $this->belongsTo(Township::class, 'township_id', 'township_id');
  }
}