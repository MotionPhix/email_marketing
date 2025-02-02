<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\EmailEvent;
use App\Services\Campaign\CampaignStatsService;
use App\Services\SendGridService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class Show extends Controller
{
  public function __construct(protected CampaignStatsService $statsService)
  {
  }

  /**
   * Handle the incoming request.
   */
  public function __invoke(Campaign $campaign, SendGridService $sendGridService)
  {
    $startDate = request('start_date', now()->subDays(7)->format('Y-m-d'));
    $endDate = request('end_date', now()->format('Y-m-d'));

    try {
      $campaignStats = $this->statsService->getStats(
        $campaign->uuid,
        $startDate,
        $endDate
      );
    } catch (\Exception $e) {
      Log::error('Error fetching campaign stats: ' . $e->getMessage());
      $campaignStats = ['error' => 'Unable to fetch statistics'];
    }

    $campaign->load([
      'template:id,uuid,name',
      'audience:id,uuid,name',
      'audience.recipients:id,uuid,name,email',
    ]);

    // Return to Inertia with campaign and stats
    return Inertia::render('Campaigns/Show', [
      'campaign' => [
        'id' => $campaign->id,
        'uuid' => $campaign->uuid,
        'title' => $campaign->title,
        'subject' => $campaign->subject,
        'description' => $campaign->description,
        'status' => $campaign->status,
        'template' => $campaign->template,
        'audience' => $campaign->audience,
        'formatted_scheduled_at' => $campaign->formatted_scheduled_at,
        'formatted_end_date' => $campaign->formatted_end_date,
      ],
      'startDate' => Carbon::parse($startDate)->toDateString(),
      'endDate' => Carbon::parse($endDate)->toDateString(),
      'statistics' => $campaignStats,
    ]);
  }
}
