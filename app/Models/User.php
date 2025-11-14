<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use App\Models\Order;
use App\Models\Township;

class User extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

  protected $primaryKey = 'user_id';

  protected $fillable = [
    'user_name',
    'phone_number',
    'user_password',
    'user_address',
    'township_id',
    'current_bottles',
    'change_return',
    'empty_collected',
    'is_active',
  ];

  protected $hidden = [
    'user_password',
    'remember_token',
  ];

  protected $dates = ['deleted_at'];

  public function orders()
  {
    return $this->hasMany(Order::class, 'user_id', 'user_id');
  }
  // User model
  public function owner()
  {
    return $this->belongsTo(Owner::class, 'owner_id');
  }

  public function getAuthPassword()
  {
    return $this->user_password;
  }
  public function township()
  {
    return $this->belongsTo(Township::class, 'township_id', 'township_id');
  }

  //user and orders relationship

  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }

  public function scopeInactive($query)
  {
    return $query->where('is_active', false);
  }

  public function deactivateAccount()
  {
    $this->update([
      'is_active' => false,
      'deleted_at' => now()
    ]);
  }

  public function reactivateAccount()
  {
    $this->update([
      'is_active' => true,
      'deleted_at' => null
    ]);
  }
  public function isActive()
  {
    return $this->is_active && is_null($this->deleted_at);
  }


  public function isDeactivated()
  {
    return !$this->is_active || !is_null($this->deleted_at);
  }
}