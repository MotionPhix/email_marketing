<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailQuota extends Model
{
  protected $fillable = [
    'user_id',
    'monthly_limit',
    'monthly_used',
    'daily_limit',
    'daily_used',
    'last_reset_at'
  ];

  protected $casts = [
    'last_reset_at' => 'datetime'
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function hasAvailableQuota(): bool
  {
    $this->checkAndResetQuotas();
    return $this->daily_used < $this->daily_limit &&
      $this->monthly_used < $this->monthly_limit;
  }

  protected function checkAndResetQuotas(): void
  {
    $now = now();

    if ($this->last_reset_at->diffInDays($now) >= 1) {
      $this->daily_used = 0;
    }

    if ($this->last_reset_at->diffInMonths($now) >= 1) {
      $this->monthly_used = 0;
    }

    if ($this->isDirty(['daily_used', 'monthly_used'])) {
      $this->last_reset_at = $now;
      $this->save();
    }
  }
}
