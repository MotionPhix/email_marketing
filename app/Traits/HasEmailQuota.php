<?php

namespace App\Traits;

use App\Models\EmailQuota;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasEmailQuota
{
  public function emailQuota(): HasOne
  {
    return $this->hasOne(EmailQuota::class);
  }

  public function initializeEmailQuota(): void
  {
    $this->emailQuota()->create([
      'monthly_limit' => config('mail.quotas.monthly_default', 1000),
      'monthly_used' => 0,
      'daily_limit' => config('mail.quotas.daily_default', 100),
      'daily_used' => 0,
      'last_reset_at' => now(),
    ]);
  }

  public function incrementEmailQuota(int $amount = 1): bool
  {
    if (!$this->canSendEmails($amount)) {
      return false;
    }

    $this->emailQuota->increment('daily_used', $amount);
    $this->emailQuota->increment('monthly_used', $amount);

    return true;
  }

  public function canSendEmails(int $amount = 1): bool
  {
    return $this->emailQuota &&
      $this->emailQuota->hasAvailableQuota() &&
      ($this->emailQuota->daily_used + $amount <= $this->emailQuota->daily_limit) &&
      ($this->emailQuota->monthly_used + $amount <= $this->emailQuota->monthly_limit);
  }

  public function getQuotaUsagePercentage(): array
  {
    if (!$this->emailQuota) {
      return [
        'daily' => 0,
        'monthly' => 0
      ];
    }

    return [
      'daily' => ($this->emailQuota->daily_used / $this->emailQuota->daily_limit) * 100,
      'monthly' => ($this->emailQuota->monthly_used / $this->emailQuota->monthly_limit) * 100
    ];
  }
}
