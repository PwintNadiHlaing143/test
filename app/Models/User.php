<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Models\Orders;

class User extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

  protected $primaryKey = 'user_id';

  protected $fillable = [
    'user_name',
    'phone_number',
    'user_password',
    'user_address',
    'township_id',
    'current_bottles',
    'change_return',
    'empty_collected'
  ];

  protected $hidden = [
    'user_password',
    'remember_token',
  ];


  public function getAuthPassword()
  {
    return $this->user_password;
  }
  public function township()
  {
    return $this->belongsTo(Township::class, 'township_id', 'township_id');
  }

  //user and orders relationship
  public function orders()
  {
    return $this->hasMany(Orders::class, 'user_id', 'user_id');
  }
}
