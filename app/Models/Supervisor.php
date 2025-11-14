<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Models\Order;
use App\Models\Product;
use App\Models\DeliveryRoute;

class Supervisor extends Authenticatable
{
  use HasFactory, HasApiTokens, Notifiable;


  protected $table = 'supervisors';


  protected $primaryKey = 'supervisor_id';

  protected $fillable = [
    'owner_id',
    'supervisor_name',
    'supervisor_phone',
    'supervisor_address',
    'supervisor_status',
    'supervisor_password',
  ];

  protected $hidden = [
    'supervisor_password',
    'remember_token',
  ];


  public function getAuthPassword()
  {
    return $this->supervisor_password;
  }

  public function owner()
  {
    return $this->belongsTo(Owner::class, 'owner_id');
  }
  // Supervisor has many delivery groups
  public function deliveryGroups()
  {
    return $this->hasMany(DeliveryGroup::class, 'supervisor_id', 'supervisor_id');
  }

  // Supervisor has many delivery routes
  public function deliveryRoutes()
  {
    return $this->hasMany(DeliveryRoute::class, 'supervisor_id', 'supervisor_id');
  }

  // Supervisor can access orders through delivery routes
  public function orders()
  {
    return $this->hasManyThrough(
      Order::class,
      DeliveryRoute::class,
      'supervisor_id', // Foreign key on delivery_routes table
      'order_id',      // Foreign key on orders table  
      'supervisor_id', // Local key on supervisors table
      'order_id'       // Local key on delivery_routes table
    );
  }

  // Pending orders that need assignment (no delivery route yet)
  public function pendingOrders()
  {
    return Order::where('order_status', 'pending')
      ->whereDoesntHave('deliveryRoutes')
      ->get();
  }

  // Assigned orders (have delivery routes)
  public function assignedOrders()
  {
    return $this->orders()
      ->with(['user', 'product'])
      ->whereHas('deliveryRoutes')
      ->get();
  }
}