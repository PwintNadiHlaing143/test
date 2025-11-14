<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryGroup extends Model
{
  use HasFactory;


  protected $table = 'delivery_group';

  protected $primaryKey = 'group_id';

  protected $fillable = [
    'group_name',
    'supervisor_id',
  ];

  // Relationship with Supervisor
  public function supervisor()
  {
    return $this->belongsTo(Supervisor::class, 'supervisor_id', 'supervisor_id');
  }

  // Relationship with DeliveryStaff
  public function deliveryStaff()
  {
    return $this->hasMany(DeliveryStaff::class, 'group_id', 'group_id');
  }

  // Add this missing relationship
  public function deliveryRoutes()
  {
    return $this->hasMany(DeliveryRoute::class, 'group_id', 'group_id');
  }

  // Count of active delivery routes
  public function getActiveRoutesCountAttribute()
  {
    return $this->deliveryRoutes()->whereIn('delivery_status', ['assigned', 'in_progress'])->count();
  }

  // Count of completed delivery routes
  public function getCompletedRoutesCountAttribute()
  {
    return $this->deliveryRoutes()->where('delivery_status', 'completed')->count();
  }
}
