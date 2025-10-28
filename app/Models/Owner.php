<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // important for auth
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Owner extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

  protected $table = 'owner'; // table name, default Laravel will pluralize

  protected $primaryKey = 'owner_id'; // if you use custom primary key

  protected $fillable = [
    'owner_name',
    'owner_phone',
  ];

  protected $hidden = [
    'owner_password',
    'remember_token',
  ];

  /**
   * Relationship: Owner has many Supervisors
   */
  public function supervisors()
  {
    return $this->hasMany(Supervisor::class, 'owner_id', 'owner_id');
  }
}
