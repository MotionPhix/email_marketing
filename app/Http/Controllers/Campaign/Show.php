<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Services\SendGridService;
use Carbon\Carbon;

class Show extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Campaign $campaign, SendGridService $sendGridService)
  {
    $startDate = now()->subDays(7)->toDateString(); // Last 7 days
    $endDate = now()->toDateString();

    return Inertia('Campaigns/Show', [
      // 'campaign' => fn() => $campaign->load('template', 'audience.recipients'),
      'campaign' => fn() => $campaign->load(
        'template:id,name,uuid',
        'audience:id,uuid,name',
        'audience.recipients:id,uuid,name,email'
        )->makeHidden(['scheduled_at', 'end_date'])->toArray() + [
          'formatted_scheduled_at' => $campaign->scheduled_at ? Carbon::parse($campaign->scheduled_at)->format('D, d M, Y') : null,
          'formatted_end_date' => $campaign->end_date ? Carbon::parse($campaign->end_date)->format('D, d M, Y') : null,
        ],
      'statistics' => fn() =>$sendGridService->getEmailStats($startDate, $endDate, $campaign->uuid)
    ]);
  }
}
