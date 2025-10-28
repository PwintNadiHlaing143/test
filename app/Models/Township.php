<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Township extends Model
{
  use HasFactory;
  protected $table = 'townships';
  protected $primaryKey = 'id';


  protected $fillable = ['name'];


  public function users()
  {
    return $this->hasMany(User::class, 'township_id', 'id');
  }
}