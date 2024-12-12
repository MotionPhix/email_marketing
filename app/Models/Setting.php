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
    'subscription_id',
    'email_from_address',
    'email_from_name',
    'sender_name',
    'timezone',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function subscription()
  {
    return $this->belongsTo(Subscription::class);
  }
}
