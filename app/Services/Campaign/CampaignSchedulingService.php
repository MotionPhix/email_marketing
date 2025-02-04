<?php

namespace App\Services\Campaign;

use App\Models\Campaign;
use App\Jobs\ScheduleCampaignJob;
use App\Events\CampaignScheduled;
use App\Events\CampaignScheduleCancelled;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CampaignSchedulingService
{
  public const FREQUENCIES = [
    'once' => null,
    'daily' => 'addDay',
    'weekly' => 'addWeek',
    'bi_weekly' => 'addWeeks',
    'monthly' => 'addMonth',
    'quarterly' => 'addMonths'
  ];

  private const DEFAULT_DURATIONS = [
    'daily' => 30,    // 30 days
    'weekly' => 90,   // ~3 months
    'bi_weekly' => 180, // ~6 months
    'monthly' => 365,  // 1 year
    'quarterly' => 730 // 2 years
  ];

  private const MAX_SCHEDULED_JOBS = 1000;
  private const CACHE_KEY_PREFIX = 'campaign_schedule:';

  public function schedule(Campaign $campaign, Carbon $startDate, string $frequency, ?Carbon $endDate = null): void
  {
    $this->validateFrequency($frequency);
    $this->validateDates($startDate, $endDate);
    $this->validateConcurrentCampaigns($campaign, $startDate);

    DB::beginTransaction();

    try {
      if ($frequency === 'once') {
        $this->scheduleJob($campaign, $startDate);
      } else {
        $endDate = $endDate ?? $this->calculateDefaultEndDate($startDate, $frequency);
        $this->validateJobCount($startDate, $endDate, $frequency);
        $this->scheduleRecurringJobs($campaign, $startDate, $frequency, $endDate);
        $this->updateCampaignStatus($campaign, $startDate, $endDate, $frequency);
      }

      DB::commit();

      $this->cacheScheduleInfo($campaign, $startDate, $frequency, $endDate);
      event(new CampaignScheduled($campaign));

    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Campaign scheduling failed', [
        'campaign_uuid' => $campaign->uuid,
        'error' => $e->getMessage()
      ]);

      throw $e;
    }
  }

  private function validateJobCount(Carbon $startDate, Carbon $endDate, string $frequency): void
  {
    $jobCount = $this->calculateJobCount($startDate, $endDate, $frequency);

    if ($jobCount > self::MAX_SCHEDULED_JOBS) {
      throw new \Exception("Schedule would create too many jobs ({$jobCount}). Maximum allowed: " . self::MAX_SCHEDULED_JOBS);
    }
  }

  private function calculateJobCount(Carbon $startDate, Carbon $endDate, string $frequency): int
  {
    $interval = match($frequency) {
      'daily' => 'P1D',
      'weekly' => 'P1W',
      'bi_weekly' => 'P2W',
      'monthly' => 'P1M',
      'quarterly' => 'P3M',
      default => throw new \InvalidArgumentException("Invalid frequency: {$frequency}")
    };

    $period = new \DatePeriod($startDate, new \DateInterval($interval), $endDate);
    return iterator_count($period) + 1;
  }

  public function cancelScheduledJobs(Campaign $campaign): void
  {
    DB::transaction(function () use ($campaign) {
      // Update campaign status
      $campaign->update([
        'status' => Campaign::STATUS_DRAFT,
        'scheduled_at' => null,
        'frequency' => null,
        'end_date' => null,
      ]);

      // Clear cache
      Cache::forget(self::CACHE_KEY_PREFIX . $campaign->uuid);

      // Log cancellation
      Log::info('Campaign schedule cancelled', [
        'campaign_uuid' => $campaign->uuid
      ]);

      // Dispatch cancellation event
      event(new CampaignScheduleCancelled($campaign));
    });
  }

  private function validateFrequency(string $frequency): void
  {
    if (!array_key_exists($frequency, self::FREQUENCIES)) {
      throw new \InvalidArgumentException("Invalid frequency: {$frequency}");
    }
  }

  private function validateDates(Carbon $startDate, ?Carbon $endDate): void
  {
    if ($startDate->isPast()) {
      throw new \Exception('Start date cannot be in the past');
    }

    if ($endDate && $endDate->isBefore($startDate)) {
      throw new \Exception('End date must be after start date');
    }
  }

  private function validateConcurrentCampaigns(Campaign $campaign, Carbon $startDate): void
  {
    $concurrentCampaigns = Campaign::where('user_id', $campaign->user_id)
      ->where('uuid', '!=', $campaign->uuid)
      ->where('scheduled_at', $startDate->format('Y-m-d'))
      ->count();

    if ($concurrentCampaigns >= 5) {
      throw new \Exception('Maximum concurrent campaigns limit reached for this date');
    }
  }

  private function scheduleJob(Campaign $campaign, Carbon $date): void
  {
    ScheduleCampaignJob::dispatch($campaign)->delay($date);
  }

  private function scheduleRecurringJobs(Campaign $campaign, Carbon $startDate, string $frequency, Carbon $endDate): void
  {
    $currentDate = $startDate->copy();
    $method = self::FREQUENCIES[$frequency];
    $interval = $frequency === 'bi_weekly' ? 2 : 3;

    while ($currentDate->lte($endDate)) {
      $this->scheduleJob($campaign, $currentDate);

      if (in_array($frequency, ['bi_weekly', 'quarterly'])) {
        $currentDate->{$method}($interval);
      } else {
        $currentDate->{$method}();
      }
    }
  }

  private function updateCampaignStatus(Campaign $campaign, Carbon $startDate, ?Carbon $endDate, string $frequency): void
  {
    $campaign->update([
      'status' => Campaign::STATUS_SCHEDULED,
      'frequency' => $frequency,
      'scheduled_at' => $startDate,
      'end_date' => $endDate,
    ]);
  }

  private function calculateDefaultEndDate(Carbon $startDate, string $frequency): Carbon
  {
    $duration = self::DEFAULT_DURATIONS[$frequency] ?? 30;
    return $startDate->copy()->addDays($duration);
  }

  private function cacheScheduleInfo(Campaign $campaign, Carbon $startDate, string $frequency, ?Carbon $endDate): void
  {
    $cacheKey = self::CACHE_KEY_PREFIX . $campaign->uuid;
    $scheduleInfo = [
      'start_date' => $startDate->toDateTimeString(),
      'frequency' => $frequency,
      'end_date' => $endDate?->toDateTimeString(),
      'last_updated' => now()->toDateTimeString(),
    ];

    Cache::put($cacheKey, $scheduleInfo, now()->addDays(90));
  }
}
