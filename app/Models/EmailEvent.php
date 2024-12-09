<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailEvent extends Model
{
  protected $fillable = [
    'email_log_id',
    'event',
    'ip',
    'user_agent',
    'url',
    'reason',
    'status',
    'timestamp'
  ];

  protected $casts = [
    'timestamp' => 'datetime',
  ];

  public function emailLog()
  {
    return $this->belongsTo(EmailLog::class);
  }
}
