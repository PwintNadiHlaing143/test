<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Township extends Model
{
  use HasFactory;
  protected $table = 'townships';
  protected $primaryKey = 'township_id';


  protected $fillable = ['township_name'];


  public function users()
  {
    return $this->hasMany(User::class, 'township_id', 'township_id');
  }
}
