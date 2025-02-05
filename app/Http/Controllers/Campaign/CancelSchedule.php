<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Services\Campaign\CampaignSchedulingService;
use Illuminate\Http\Request;

class CancelSchedule extends Controller
{
  public function __construct(
    private readonly CampaignSchedulingService $schedulingService
  ) {}

  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Campaign $campaign)
  {
    try {
      // Ensure the campaign belongs to the authenticated user
      if ($campaign->user_id !== auth()->id()) {
        abort(403, 'Unauthorized action.');
      }

      // Ensure the campaign is in a cancellable state
      if (!in_array($campaign->status, [Campaign::STATUS_SCHEDULED, Campaign::STATUS_SENDING])) {
        return back()->with('error', 'This campaign cannot be cancelled.');
      }

      $this->schedulingService->cancelScheduledJobs($campaign);

      return back()->with('success', 'Campaign schedule cancelled successfully');
    } catch (\Exception $e) {
      report($e); // Log the error
      return back()->with('error', 'Failed to cancel campaign schedule');
    }
  }
}
