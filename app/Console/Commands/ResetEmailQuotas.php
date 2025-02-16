<?php

namespace App\Console\Commands;

use App\Models\EmailQuota;
use Illuminate\Console\Command;

class ResetEmailQuotas extends Command
{
  protected $signature = 'email:reset-quotas';
  protected $description = 'Reset email quotas based on their reset periods';

  public function handle()
  {
    $now = now();

    EmailQuota::chunk(100, function ($quotas) use ($now) {
      foreach ($quotas as $quota) {
        if ($quota->last_reset_at->diffInDays($now) >= 1) {
          $quota->daily_used = 0;
        }

        if ($quota->last_reset_at->diffInMonths($now) >= 1) {
          $quota->monthly_used = 0;
        }

        if ($quota->isDirty(['daily_used', 'monthly_used'])) {
          $quota->last_reset_at = $now;
          $quota->save();
        }
      }
    });

    $this->info('Email quotas have been reset successfully.');
  }
}
