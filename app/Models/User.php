<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
  protected $table = 'users'; // table name
  protected $primaryKey = 'id'; // primary key

  // fillable fields (optional)
  protected $fillable = [
    'name',
    'email',
    'password',
    'township_id',
  ];

  // Township relationship
  public function township()
  {
    return $this->belongsTo(Township::class, 'township_id', 'township_id');
  }
}
