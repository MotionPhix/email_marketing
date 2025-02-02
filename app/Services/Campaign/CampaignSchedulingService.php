<?php

namespace App\Services\Campaign;

use App\Models\Campaign;
use App\Jobs\ScheduleCampaignJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CampaignSchedulingService
{
  /**
   * Available scheduling frequencies and their interval methods
   */
  public const FREQUENCIES = [
    'once' => null,
    'daily' => 'addDay',
    'weekly' => 'addWeek',
    'bi_weekly' => 'addWeeks',
    'monthly' => 'addMonth',
    'quarterly' => 'addMonths'
  ];

  /**
   * Default duration in days for each frequency if no end date is provided
   */
  private const DEFAULT_DURATIONS = [
    'daily' => 30,    // 30 days
    'weekly' => 90,   // ~3 months
    'bi_weekly' => 180, // ~6 months
    'monthly' => 365,  // 1 year
    'quarterly' => 730 // 2 years
  ];

  /**
   * Schedule a campaign with the specified parameters
   *
   * @param Campaign $campaign
   * @param Carbon $startDate
   * @param string $frequency
   * @param Carbon|null $endDate
   * @throws \InvalidArgumentException
   * @throws \Exception
   */
  public function schedule(Campaign $campaign, Carbon $startDate, string $frequency, ?Carbon $endDate = null): void
  {
    $this->validateFrequency($frequency);
    $this->validateDates($startDate, $endDate);

    // For one-time campaigns, just schedule a single job
    if ($frequency === 'once') {
      $this->scheduleJob($campaign, $startDate);
      return;
    }

    // Calculate end date if not provided
    $endDate = $endDate ?? $this->calculateDefaultEndDate($startDate, $frequency);

    // Schedule recurring jobs
    $this->scheduleRecurringJobs($campaign, $startDate, $frequency, $endDate);
  }

  /**
   * Validate the scheduling frequency
   *
   * @param string $frequency
   * @throws \InvalidArgumentException
   */
  private function validateFrequency(string $frequency): void
  {
    if (!array_key_exists($frequency, self::FREQUENCIES)) {
      throw new \InvalidArgumentException("Invalid frequency: {$frequency}");
    }
  }

  /**
   * Validate start and end dates
   *
   * @param Carbon $startDate
   * @param Carbon|null $endDate
   * @throws \Exception
   */
  private function validateDates(Carbon $startDate, ?Carbon $endDate): void
  {
    if ($startDate->isPast()) {
      throw new \Exception('Start date cannot be in the past');
    }

    if ($endDate && $endDate->isBefore($startDate)) {
      throw new \Exception('End date must be after start date');
    }

    if ($startDate->timestamp > 2147483647 || ($endDate && $endDate->timestamp > 2147483647)) {
      throw new \Exception('Scheduled date exceeds maximum allowed timestamp');
    }
  }

  /**
   * Calculate default end date based on frequency
   *
   * @param Carbon $startDate
   * @param string $frequency
   * @return Carbon
   */
  private function calculateDefaultEndDate(Carbon $startDate, string $frequency): Carbon
  {
    $duration = self::DEFAULT_DURATIONS[$frequency] ?? 30;
    return $startDate->copy()->addDays($duration);
  }

  /**
   * Schedule a single campaign job
   *
   * @param Campaign $campaign
   * @param Carbon $date
   */
  private function scheduleJob(Campaign $campaign, Carbon $date): void
  {
    Log::info('Scheduling campaign job', [
      'campaign_uuid' => $campaign->uuid,
      'scheduled_for' => $date->toDateTimeString(),
    ]);

    ScheduleCampaignJob::dispatch($campaign)->delay($date);
  }

  /**
   * Schedule recurring campaign jobs
   *
   * @param Campaign $campaign
   * @param Carbon $startDate
   * @param string $frequency
   * @param Carbon $endDate
   */
  private function scheduleRecurringJobs(Campaign $campaign, Carbon $startDate, string $frequency, Carbon $endDate): void
  {
    $currentDate = $startDate->copy();
    $method = self::FREQUENCIES[$frequency];
    $interval = $frequency === 'bi_weekly' ? 2 : 3;

    while ($currentDate->lte($endDate)) {
      $this->scheduleJob($campaign, $currentDate);

      // Handle special cases for bi-weekly and quarterly
      if (in_array($frequency, ['bi_weekly', 'quarterly'])) {
        $currentDate->{$method}($interval);
      } else {
        $currentDate->{$method}();
      }
    }
  }

  /**
   * Cancel all scheduled jobs for a campaign
   *
   * @param Campaign $campaign
   */
  public function cancelScheduledJobs(Campaign $campaign): void
  {
    // Implementation depends on your queue driver
    // For database queue:
    // Queue::where('payload->command', 'like', '%ScheduleCampaignJob%')
    //     ->where('payload', 'like', "%{$campaign->uuid}%")
    //     ->delete();
  }
}
