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

  public function supervisor()
  {
    return $this->belongsTo(Supervisor::class, 'supervisor_id', 'supervisor_id');
  }


  public function deliveryStaff()
  {
    return $this->hasMany(DeliveryStaff::class, 'group_id', 'group_id');
  }
}