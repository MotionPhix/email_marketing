<?php

namespace App\Modules\Billing\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Subscription extends Model
{
  use HasUuids;

  public const STATUS_ACTIVE = 'active';
  public const STATUS_CANCELLED = 'cancelled';
  public const STATUS_EXPIRED = 'expired';
  public const STATUS_SCHEDULED = 'scheduled';
  public const STATUS_PENDING = 'pending';

  protected $fillable = [
    'user_id',
    'plan_id',
    'status',
    'trial_ends_at',
    'starts_at',
    'ends_at',
    'cancelled_at',
    'last_payment_at',
    'paychangu_transaction_id',
    'paychangu_payment_status',
    'payment_metadata'
  ];

  protected $casts = [
    'trial_ends_at' => 'datetime',
    'starts_at' => 'datetime',
    'ends_at' => 'datetime',
    'cancelled_at' => 'datetime',
    'last_payment_at' => 'datetime',
    'payment_metadata' => 'array',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function plan(): BelongsTo
  {
    return $this->belongsTo(Plan::class);
  }

  public function isActive(): bool
  {
    return $this->status === self::STATUS_ACTIVE;
  }

  public function isCancelled(): bool
  {
    return $this->status === self::STATUS_CANCELLED;
  }

  public function isExpired(): bool
  {
    return $this->status === self::STATUS_EXPIRED;
  }

  public function isScheduled(): bool
  {
    return $this->status === self::STATUS_SCHEDULED;
  }

  public function isPending(): bool
  {
    return $this->status === self::STATUS_PENDING;
  }

  public function onTrial(): bool
  {
    return $this->trial_ends_at?->isFuture() ?? false;
  }

  public function onGracePeriod(): bool
  {
    return $this->isCancelled() && $this->ends_at?->isFuture() ?? false;
  }

  public function hasPaymentFailed(): bool
  {
    return $this->paychangu_payment_status === 'failed';
  }

  public function hasValidPayment(): bool
  {
    return $this->paychangu_payment_status === 'completed';
  }
}
