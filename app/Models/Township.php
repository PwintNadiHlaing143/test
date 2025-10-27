<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // ← ဒီလို import
use Illuminate\Database\Eloquent\Model;

class Township extends Model
{
  use HasFactory; // ← ဒီလို trait သုံး

  protected $table = 'townships';
  protected $primaryKey = 'id';

  // optional
  protected $fillable = ['name'];

  // Users relationship
  public function users()
  {
    return $this->hasMany(User::class, 'township_id', 'id');
  }
}
