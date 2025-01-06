<?php

namespace App\Models;

use App\Traits\BootUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
  use HasFactory, BootUuid;

  protected $fillable = [
    'user_id',
    'plan_id',
    'email_from_address',
    'email_from_name',
    'sender_name',
    'timezone',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function plan()
  {
    return $this->belongsTo(Plan::class);
  }
}
