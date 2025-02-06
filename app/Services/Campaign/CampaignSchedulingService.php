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
    'daily' => ['method' => 'addDay', 'default_duration' => 30],    // 30 days
    'weekly' => ['method' => 'addWeek', 'default_duration' => 90],   // ~3 months
    'bi_weekly' => ['method' => 'addWeeks', 'default_duration' => 180], // ~6 months
    'monthly' => ['method' => 'addMonth', 'default_duration' => 365],  // 1 year
    'quarterly' => ['method' => 'addMonths', 'default_duration' => 730] // 2 years
  ];

  private const MAX_SCHEDULED_JOBS = 1000;
  private const CACHE_KEY_PREFIX = 'campaign_schedule:';

  public function schedule(Campaign $campaign, Carbon $startDate, string $frequency, ?Carbon $endDate = null): void
  {
    // Verify user has a paid plan
    if (!$campaign->user->hasPaidPlan()) {
      throw new \Exception('Campaign scheduling is only available for paid plans.');
    }

    $this->validateFrequency($frequency);
    $this->validateDates($startDate, $endDate);
    $this->validateConcurrentCampaigns($campaign, $startDate);

    // Calculate default end date if not provided
    if (!$endDate && isset(self::FREQUENCIES[$frequency])) {
      $duration = self::FREQUENCIES[$frequency]['default_duration'];
      $endDate = $startDate->copy()->addDays($duration);
    }

    DB::beginTransaction();

    try {
      $this->validateJobCount($startDate, $endDate, $frequency);
      $this->scheduleRecurringJobs($campaign, $startDate, $frequency, $endDate);
      $this->updateCampaignStatus($campaign, $startDate, $endDate, $frequency);

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

  private function scheduleRecurringJobs(Campaign $campaign, Carbon $startDate, string $frequency, ?Carbon $endDate): void
  {
    $currentDate = $startDate->copy();
    $method = self::FREQUENCIES[$frequency]['method'];
    $jobCount = 0;

    while ($currentDate <= $endDate && $jobCount < self::MAX_SCHEDULED_JOBS) {
      dispatch(new ScheduleCampaignJob(
        $campaign,
        $currentDate->copy(),
        $frequency,
        $endDate,
        $campaign->user_id // Pass the user ID from the campaign
      ));

      $currentDate = $currentDate->copy()->$method(
        $frequency === 'bi_weekly' ? 2 :
          ($frequency === 'quarterly' ? 3 : 1)
      );
      $jobCount++;
    }
  }

  private function validateFrequency(string $frequency): void
  {
    if (!isset(self::FREQUENCIES[$frequency])) {
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
      ->where('status', Campaign::STATUS_SCHEDULED)
      ->whereDate('scheduled_at', $startDate->toDateString())
      ->count();

    if ($concurrentCampaigns >= 5) {
      throw new \Exception('Maximum concurrent campaigns limit reached for this date');
    }
  }

  private function updateCampaignStatus(Campaign $campaign, Carbon $startDate, ?Carbon $endDate, string $frequency): void
  {
    $campaign->update([
      'status' => Campaign::STATUS_SCHEDULED,
      'scheduled_at' => $startDate,
      'end_date' => $endDate,
      'frequency' => $frequency,
    ]);
  }

  private function cacheScheduleInfo(Campaign $campaign, Carbon $startDate, string $frequency, ?Carbon $endDate): void
  {
    $cacheKey = self::CACHE_KEY_PREFIX . $campaign->uuid;
    Cache::put($cacheKey, [
      'start_date' => $startDate->toDateTimeString(),
      'frequency' => $frequency,
      'end_date' => $endDate?->toDateTimeString()
    ], now()->addDays(30));
  }

  public function cancelScheduledJobs(Campaign $campaign): void
  {
    DB::transaction(function () use ($campaign) {
      $campaign->update([
        'status' => Campaign::STATUS_DRAFT,
        'scheduled_at' => null,
        'end_date' => null,
        'frequency' => null
      ]);

      Cache::forget(self::CACHE_KEY_PREFIX . $campaign->uuid);
      event(new CampaignScheduleCancelled($campaign));
    });
  }

  /**
   * Validate that the number of jobs to be created doesn't exceed the maximum limit
   */
  private function validateJobCount(Carbon $startDate, ?Carbon $endDate, string $frequency): void
  {
    if (!$endDate) {
      return;
    }

    $jobCount = 0;
    $currentDate = $startDate->copy();

    while ($currentDate <= $endDate) {
      $jobCount++;

      if ($jobCount > self::MAX_SCHEDULED_JOBS) {
        throw new \Exception(
          "Schedule would create too many jobs. Maximum allowed is " . self::MAX_SCHEDULED_JOBS
        );
      }

      // Increment date based on frequency
      switch ($frequency) {
        case 'daily':
          $currentDate->addDay();
          break;
        case 'weekly':
          $currentDate->addWeek();
          break;
        case 'bi_weekly':
          $currentDate->addWeeks(2);
          break;
        case 'monthly':
          $currentDate->addMonth();
          break;
        case 'quarterly':
          $currentDate->addMonths(3);
          break;
      }
    }
  }
}
