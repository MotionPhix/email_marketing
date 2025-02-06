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

  protected $appends = [
    'formatted_features'
  ];

  // Subscription statuses
  const STATUS_ACTIVE = 'active';
  const STATUS_CANCELLED = 'cancelled';
  const STATUS_SCHEDULED = 'scheduled';
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

  /**
   * Get the formatted features from the associated plan
   */
  public function getFormattedFeaturesAttribute(): array
  {
    if (!$this->plan) {
      return [];
    }

    // Check if plan has features property and it's an array
    $features = $this->plan->features;
    if (!is_array($features)) {
      // If features is stored as JSON string, decode it
      $features = json_decode($this->plan->features, true) ?? [];
    }

    // Format each feature
    return array_map(function ($feature) {
      return [
        'name' => $feature['name'] ?? '',
        'description' => $feature['description'] ?? '',
        'included' => $feature['included'] ?? true,
        'value' => $feature['value'] ?? null,
        'icon' => $feature['icon'] ?? null,
      ];
    }, $features);
  }

  public function latestRenewal()
  {
    return $this->hasOne(SubscriptionRenewal::class)->latest();
  }

  /**
   * Check if the subscription is active
   */
  public function isActive(): bool
  {
    return $this->status === self::STATUS_ACTIVE &&
      ($this->ends_at === null || $this->ends_at->isFuture());
  }

  public function isOnTrial()
  {
    return $this->status === self::STATUS_TRIAL;
  }

  public function hasExpired()
  {
    return $this->ends_at?->isPast() || $this->status === self::STATUS_EXPIRED;
  }

  /**
   * Check if the subscription has ended
   */
  public function hasEnded(): bool
  {
    return $this->ends_at !== null && $this->ends_at->isPast();
  }

  /**
   * Check if the subscription is pending
   */
  public function isPending(): bool
  {
    return $this->status === self::STATUS_PENDING;
  }

  /**
   * Check if the subscription is cancelled
   */
  public function isCancelled(): bool
  {
    return $this->status === self::STATUS_CANCELLED;
  }

  /**
   * Check if the subscription is expired
   */
  public function isExpired(): bool
  {
    return $this->status === self::STATUS_EXPIRED;
  }

  /**
   * Get the subscription's remaining days
   */
  public function getRemainingDays(): int
  {
    if (!$this->ends_at) {
      return 0;
    }

    return max(0, now()->diffInDays($this->ends_at, false));
  }
}
