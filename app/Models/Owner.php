<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // important for auth
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Owner extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

  protected $table = 'owner';

  protected $primaryKey = 'owner_id';
  protected $fillable = [
    'owner_name',
    'owner_phone',
  ];

  protected $hidden = [
    'owner_password',
    'remember_token',
  ];

  public function supervisors()
  {
    return $this->hasMany(Supervisor::class, 'owner_id', 'owner_id');
  }
}