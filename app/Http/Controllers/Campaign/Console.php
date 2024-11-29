<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Jobs\ScheduleCampaignJob;
use App\Models\Campaign;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Console extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Campaign $campaign)
  {
    $validated = $request->validate([
      'scheduled_at' => 'required|array',
      'frequency' => 'required|in:bi_weekly,weekly,monthly,quarterly',
      'stop_campaign' => 'nullable|boolean',
      'end_date' => 'nullable|array',
    ]);

    if (is_array($validated['scheduled_at'])) {

      $validated['scheduled_at'] = Carbon::createFromDate(
        $validated['scheduled_at']['year'],
        $validated['scheduled_at']['month'],
        $validated['scheduled_at']['day']
      );

    }

    if ((bool)$validated['stop_campaign'] === true) {

      $validated['end_date'] = Carbon::now();

    } else {

      $validated['end_date'] = $validated['scheduled_at']->copy()->addYear();

    }

    try {

      $this->scheduleCampaigns(
        $campaign,
        $validated['scheduled_at'],
        $validated['end_date'],
        $validated['frequency']
      );

      $campaign->update([
        'scheduled_at' => $validated['scheduled_at'],
        'frequency' => $validated['frequency'],
        'end_date' => $validated['end_date'],
      ]);

      return $this->redirectWithFlash('success', 'Campaign was scheduled successfully!');

    } catch (\Throwable $e) {

      logger()->error('Failed to schedule campaign', [
        'error' => $e->getMessage(),
        'campaign_id' => $campaign->id,
      ]);

      return $this->redirectWithFlash(
        'danger',
        'Failed to schedule campaign. Please try again.'
      );

    }

  }

  /**
   * Schedule campaign jobs based on the interval.
   */
  private function scheduleCampaigns(
    Campaign $campaign,
    Carbon $startDate,
    ?Carbon $endDate,
    string $frequency
  ) {
    $intervalMethod = match ($frequency) {
      'weekly' => fn (&$date) => $date->addWeek(),
      'bi_weekly' => fn (&$date) => $date->addWeeks(2),
      'monthly' => fn (&$date) => $date->addMonth(),
      'quarterly' => fn (&$date) => $date->addMonths(3),
      default => throw new \InvalidArgumentException('Unsupported frequency: ' . $frequency),
    };

    $currentDate = $startDate;

    while (!$endDate || $currentDate <= $endDate) {

      logger()->info('Scheduling job with timestamp', [
        'timestamp' => $currentDate->timestamp,
        'date' => $currentDate->toDateTimeString(),
      ]);

      if ($currentDate->timestamp > 2147483647) {

        throw new \Exception('Scheduled date exceeds MySQL timestamp range.');

      }

      ScheduleCampaignJob::dispatch($campaign)->delay($currentDate);
      $intervalMethod($currentDate);
    }
  }

  /**
   * Redirect with a flash message.
   */
  private function redirectWithFlash(string $style, string $message)
  {
    return redirect()->back()->with('flash', [
      'bannerStyle' => $style,
      'banner' => $message,
    ]);
  }
}
