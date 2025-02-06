<?php

namespace App\Models;

use App\Traits\BootUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionRenewal extends Model
{
  use HasFactory, BootUuid;

  protected $fillable = [
    'subscription_id',
    'paychangu_reference',
    'amount',
    'status',
    'completed_at',
    'failed_at'
  ];

  protected $casts = [
    'amount' => 'integer',
    'completed_at' => 'datetime',
    'failed_at' => 'datetime'
  ];

  public function subscription()
  {
    return $this->belongsTo(Subscription::class);
  }
}
