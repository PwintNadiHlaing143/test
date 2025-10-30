<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;

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
}