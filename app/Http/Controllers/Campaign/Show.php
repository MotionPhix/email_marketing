<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\EmailEvent;
use App\Services\SendGridService;
use Carbon\Carbon;
use Inertia\Inertia;

class Show extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Campaign $campaign, SendGridService $sendGridService)
  {
    $startDate = request('start_date', now()->subDays(7)->format('Y-m-d'));
    $endDate = request('end_date', now()->format('Y-m-d'));

    $startDate = Carbon::parse($startDate)->startOfDay();
    $endDate = Carbon::parse($endDate)->endOfDay();

    try {
      $campaignStats = $this->getCampaignStats($campaign->uuid, $startDate, $endDate);
    } catch (\Exception $e) {
      \Log::error('Error fetching campaign stats: ' . $e->getMessage());
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
        'status' => $campaign->status,
        'template' => $campaign->template,
        'audience' => $campaign->audience,
        'formatted_scheduled_at' => $campaign->formatted_scheduled_at,
        'formatted_end_date' => $campaign->formatted_end_date,
      ],
      'startDate' => $startDate->toDateString(),
      'endDate' => $endDate->toDateString(),
      'statistics' => $campaignStats,
    ]);
  }

  /**
   * Get the campaign stats from the EmailEvents table.
   */
  private function getCampaignStats($campaignUuid, $startDate, $endDate)
  {
    $stats = EmailEvent::whereHas('emailLog', function ($query) use ($campaignUuid) {
      $query->where('campaign_uuid', $campaignUuid);
    })
      ->whereBetween('timestamp', [$startDate, $endDate])
      ->selectRaw('DATE(timestamp) as date, event, COUNT(*) as total')
      ->groupBy('date', 'event')
      ->get()
      ->groupBy('date')
      ->map(function ($dateGroup) {
        return $dateGroup->keyBy('event')->map(fn($item) => $item->total);
      });

    $summaryStats = [
      'opened' => $this->getEventCount($stats, 'open'), // New unique clicks count
      'clicked' => $this->getEventCount($stats, 'click'),
      'unique_opened' => $this->getUniqueEventCount('open', $startDate, $endDate, $campaignUuid),
      'unique_clicked' => $this->getUniqueEventCount('click', $startDate, $endDate, $campaignUuid), // New unique clicks count
      'bounced' => $this->getEventCount($stats, 'bounce'),
      'spam_report' => $this->getEventCount($stats, 'spam_report'),
      'delivered' => $this->getEventCount($stats, 'delivered'),
    ];

    $chartData = $this->prepareChartData($stats, $startDate, $endDate);

    return [
      'stats' => $summaryStats,
      'chart' => $chartData,
    ];
  }

  /**
   * Helper function to get the count for a specific event type.
   */
  private function getEventCount($stats, $eventType)
  {
    return $stats->flatMap(fn($dateStats) => $dateStats->only($eventType))->sum();
  }

  /**
   * Prepare chart data for graph over time.
   * Format: [date => [opened => 10, clicked => 5, ...], ...]
   */
  private function prepareChartData($stats, $startDate, $endDate)
  {
    $dateRange = $this->generateDateRange($startDate, $endDate);

    $chartData = [];
    foreach ($dateRange as $date) {
      $chartData[$date] = [
        'open' => 0,
        'click' => 0,
        'bounce' => 0,
        'spam_report' => 0,
        'delivered' => 0,
      ];
    }

    foreach ($stats as $date => $dateStats) {
      foreach ($dateStats as $event => $count) {
        \Log::info($event, [$event => $count]);
        if (isset($chartData[$date][$event])) {
          $chartData[$date][$event] = $count;
        }
      }
    }

    \Log::debug($chartData);

    return $chartData;
  }

  /**
   * Generate a range of dates from start to end.
   */
  private function generateDateRange($startDate, $endDate)
  {
    $dates = [];
    $currentDate = $startDate->copy();

    while ($currentDate <= $endDate) {
      $dates[] = $currentDate->format('Y-m-d');
      $currentDate->addDay();
    }

    return $dates;
  }

  private function getUniqueEventCount($event, $startDate, $endDate, $campaignId)
  {
    return EmailEvent::whereHas('emailLog', function ($query) use($campaignId) {
      $query->where('campaign_uuid', $campaignId);
    })
      ->where('event', $event)
      ->whereBetween('timestamp', [$startDate, $endDate])
      ->join('email_logs', 'email_events.email_log_id', '=', 'email_logs.id') // Join to access recipient_id
      ->selectRaw('COUNT(DISTINCT email_logs.email) as total')
      ->value('total');
  }
}
