<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryRoute extends Model
{
  use HasFactory;

  protected $primaryKey = 'route_id';

  protected $fillable = [
    'group_id',
    'order_id',
    'supervisor_id',
    'township_id',
    'delivery_status',
    'delivery_date',

  ];

  protected $casts = [
    'delivery_date' => 'date',
  ];

  // Relationships
  public function supervisor()
  {
    return $this->belongsTo(Supervisor::class, 'supervisor_id', 'supervisor_id');
  }

  public function order()
  {
    return $this->belongsTo(Order::class, 'order_id', 'order_id');
  }

  public function deliveryGroup()
  {
    return $this->belongsTo(DeliveryGroup::class, 'group_id', 'group_id');
  }

  public function township()
  {
    return $this->belongsTo(Township::class, 'township_id', 'township_id');
  }

  // Simple scopes
  public function scopePending($query)
  {
    return $query->where('delivery_status', 'pending');
  }

  public function scopeAssigned($query)
  {
    return $query->where('delivery_status', 'assigned');
  }

  public function scopeDelivered($query)
  {
    return $query->where('delivery_status', 'delivered');
  }

  public function scopeToday($query)
  {
    return $query->whereDate('delivery_date', today());
  }

  // Status check methods
  public function isPending()
  {
    return $this->delivery_status === 'pending';
  }

  public function isAssigned()
  {
    return $this->delivery_status === 'assigned';
  }

  public function isDelivered()
  {
    return $this->delivery_status === 'completed';
  }
}