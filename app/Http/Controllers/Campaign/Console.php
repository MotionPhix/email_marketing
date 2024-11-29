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
    $startDate = Carbon::parse($campaign->scheduled_at);
    $endDate = $campaign->end_date ? Carbon::parse($campaign->end_date) : null;

    switch ($campaign->frequency) {
      case 'daily':
        $this->scheduleCampaigns($campaign, $startDate, $endDate, '1 day');
        break;

      case 'weekly':
        $this->scheduleCampaigns($campaign, $startDate, $endDate, '1 week');
        break;

      case 'monthly':
        $this->scheduleCampaigns($campaign, $startDate, $endDate, '1 month');
        break;

      default:
        return response()->json(['message' => 'Invalid frequency'], 400);
    }

    return response()->json(['message' => 'Campaign scheduled successfully']);
  }

  /**
   * Schedule campaign jobs based on the interval.
   */
  private function scheduleCampaigns(Campaign $campaign, Carbon $startDate, ?Carbon $endDate, string $interval)
  {
    $currentDate = $startDate;

    while (!$endDate) {
      // || $currentDate->lessThanOrEqualTo($endDate)
      ScheduleCampaignJob::dispatch($campaign)->delay($currentDate);
      $currentDate->add($interval);
    }
  }
}
