<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
  use HasFactory;

  protected $table = 'orders';
  protected $primaryKey = 'order_id';

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
    'notes',
    'delivered_bottles',
    'remaining_bottles',
    'user_decision',
  ];

  protected $casts = [
    'order_date' => 'datetime',
    'total_amount' => 'decimal:2',
    'sold_price' => 'decimal:2',
    'cash_collected' => 'decimal:2',
    'change_returned' => 'decimal:2',
    'empty_collected' => 'integer',
    'delivered_bottles' => 'integer',
    'remaining_bottles' => 'integer',
  ];

  // Relationships with user
  public function user()
  {
    return $this->belongsTo(User::class, 'user_id', 'user_id')->withTrashed();
  }

  //relationship with product
  public function product()
  {
    return $this->belongsTo(Product::class, 'product_id', 'product_id');
  }

  public function owner()
  {
    return $this->belongsTo(Owner::class, 'owner_id', 'owner_id');
  }


  //relation with delivery routes
  public function deliveryRoutes()
  {
    return $this->hasMany(DeliveryRoute::class, 'order_id', 'order_id');
  }

  // Scopes
  public function scopeActiveUsers($query)
  {
    return $query->whereHas('user', function ($q) {
      $q->active();
    });
  }

  public function scopeWithInactiveUsers($query)
  {
    return $query->whereHas('user', function ($q) {
      $q->withTrashed();
    });
  }

  public function scopePending($query)
  {
    return $query->where('order_status', 'pending');
  }

  public function scopeProcessing($query)
  {
    return $query->where('order_status', 'processing');
  }

  public function scopeDelivered($query)
  {
    return $query->where('order_status', 'completed');
  }

  public function scopeCancelled($query)
  {
    return $query->where('order_status', 'cancelled');
  }

  // Helper Methods
  public function isPending()
  {
    return $this->order_status === 'pending';
  }

  public function isDelivered()
  {
    return $this->order_status === 'completed';
  }

  public function isCancelled()
  {
    return $this->order_status === 'cancelled';
  }


  public function getFormattedTotalAttribute()
  {
    // Check if total_amount is not null and convert to float
    if ($this->total_amount !== null) {
      return number_format((float)$this->total_amount, 2) . ' MMK';
    }

    return '0.00 MMK';
  }


  public function getFormattedOrderDateAttribute()
  {
    // Check if order_date exists and is a valid date
    if ($this->order_date) {
      return $this->order_date->format('M d, Y h:i A');
    }

    return 'N/A';
  }

  public function getFormattedSoldPriceAttribute()
  {
    if ($this->sold_price !== null) {
      return number_format((float)$this->sold_price, 2) . ' MMK';
    }

    return '0.00 MMK';
  }


  public function getFormattedCashCollectedAttribute()
  {
    if ($this->cash_collected !== null) {
      return number_format((float)$this->cash_collected, 2) . ' MMK';
    }

    return '0.00 MMK';
  }


  public function getFormattedChangeReturnedAttribute()
  {
    if ($this->change_returned !== null) {
      return number_format((float)$this->change_returned, 2) . ' MMK';
    }

    return '0.00 MMK';
  }
}