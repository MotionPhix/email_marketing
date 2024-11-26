<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Services\SendGridService;

class Show extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Campaign $campaign, SendGridService $sendGridService)
  {
    $startDate = now()->subDays(7)->toDateString(); // Last 7 days
    $endDate = now()->toDateString();

    $stats = $sendGridService->getEmailStats($startDate, $endDate, $campaign->uuid);

    return Inertia('Campaigns/Show', [
      'campaign' => fn() => $campaign->load('template', 'audience'),
      'statistics' => fn() => $stats
    ]);
  }
}
