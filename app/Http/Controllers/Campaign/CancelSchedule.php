<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Services\Campaign\CampaignSchedulingService;
use Illuminate\Http\Request;

class CancelSchedule extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Campaign $campaign)
  {
    try {
      app(CampaignSchedulingService::class)->cancelScheduledJobs($campaign);

      return back()->with('success', 'Campaign schedule cancelled successfully');
    } catch (\Exception $e) {
      return back()->with('error', 'Failed to cancel campaign schedule');
    }
  }
}
