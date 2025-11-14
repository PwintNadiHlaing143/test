<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
  use HasFactory;

  protected $primaryKey = 'notification_id';

  protected $fillable = [
    'user_id',
    'supervisor_id',
    'noti_title',
    'noti_message',
    'is_read',
    'from',
    'to',
    'created_by'
  ];

  protected $casts = [
    'is_read' => 'boolean',
    'created_at' => 'datetime'
  ];

  // Relationships
  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function supervisor()
  {
    return $this->belongsTo(Supervisor::class, 'supervisor_id');
  }

  public function sender()
  {
    return $this->belongsTo(User::class, 'from');
  }

  public function receiver()
  {
    return $this->belongsTo(User::class, 'to');
  }
}