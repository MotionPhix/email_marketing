<?php

namespace App\Models;

use App\Traits\BootUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
  use HasFactory, BootUuid;

  protected $fillable = [
    'user_id',
    'plan_id',
    'status',
    'paychangu_reference',
    'starts_at',
    'ends_at',
    'trial_ends_at',
    'cancelled_at',
    'payment_method',
    'auto_renew',
    'renewal_notified_at',
    'last_payment_at'
  ];

  protected $casts = [
    'starts_at' => 'datetime',
    'ends_at' => 'datetime',
    'trial_ends_at' => 'datetime',
    'cancelled_at' => 'datetime',
    'auto_renew' => 'boolean',
    'renewal_notified_at' => 'datetime',
    'last_payment_at' => 'datetime',
  ];

  // Subscription statuses
  const STATUS_ACTIVE = 'active';
  const STATUS_CANCELLED = 'cancelled';
  const STATUS_EXPIRED = 'expired';
  const STATUS_TRIAL = 'trial';
  const STATUS_PENDING = 'pending';

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function plan()
  {
    return $this->belongsTo(Plan::class);
  }

  public function renewals()
  {
    return $this->hasMany(SubscriptionRenewal::class);
  }

  public function latestRenewal()
  {
    return $this->hasOne(SubscriptionRenewal::class)->latest();
  }

  public function isActive()
  {
    return $this->status === self::STATUS_ACTIVE;
  }

  public function isOnTrial()
  {
    return $this->status === self::STATUS_TRIAL;
  }

  public function hasExpired()
  {
    return $this->ends_at?->isPast() || $this->status === self::STATUS_EXPIRED;
  }
}
