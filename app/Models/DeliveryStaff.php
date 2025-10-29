<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class DeliveryStaff extends Authenticatable
{
  use HasFactory, HasApiTokens, Notifiable;

  protected $table = 'delivery_staff';

  protected $primaryKey = 'staff_id';

  protected $fillable = [
    'group_id',
    'staff_name',
    'staff_phone',
    'staff_address',
    'staff_password',
    'staff_status',
  ];

  protected $hidden = [
    'staff_password',
    'remember_token',
  ];

  /**
   * Get the password for the user.
   */
  public function getAuthPassword()
  {
    return $this->staff_password;
  }

  /**
   * Relationship: Delivery staff belongs to a delivery group
   */
  public function deliveryGroup()
  {
    return $this->belongsTo(DeliveryGroup::class, 'group_id', 'group_id');
  }

  /**
   * Relationship: Delivery staff belongs to a supervisor through delivery group
   */
  public function supervisor()
  {
    return $this->hasOneThrough(
      Supervisor::class,
      DeliveryGroup::class,
      'group_id', // Foreign key on delivery_group table
      'supervisor_id', // Foreign key on supervisors table  
      'group_id', // Local key on delivery_staff table
      'supervisor_id' // Local key on delivery_group table
    );
  }
}