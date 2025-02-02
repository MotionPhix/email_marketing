<?php

namespace App\Services\Campaign;

use App\Models\EmailEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CampaignStatsService
{
  /**
   * Get complete statistics for a campaign
   */
  public function getStats($campaignUuid, $startDate, $endDate)
  {
    $stats = $this->getRawStats($campaignUuid, $startDate, $endDate);

    $summaryStats = [
      'opened' => $this->getEventCount($stats, 'open'),
      'clicked' => $this->getEventCount($stats, 'click'),
      'unique_opened' => $this->getUniqueEventCount('open', $startDate, $endDate, $campaignUuid),
      'unique_clicked' => $this->getUniqueEventCount('click', $startDate, $endDate, $campaignUuid),
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
   * Get raw stats from EmailEvents
   */
  private function getRawStats($campaignUuid, $startDate, $endDate)
  {
    return EmailEvent::whereHas('emailLog', function ($query) use ($campaignUuid) {
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
  }

  /**
   * Get count for a specific event type
   */
  private function getEventCount($stats, $eventType)
  {
    return $stats->flatMap(fn($dateStats) => $dateStats->only($eventType))->sum();
  }

  /**
   * Get unique event count
   */
  private function getUniqueEventCount($event, $startDate, $endDate, $campaignId)
  {
    return EmailEvent::whereHas('emailLog', function ($query) use($campaignId) {
      $query->where('campaign_uuid', $campaignId);
    })
      ->where('event', $event)
      ->whereBetween('timestamp', [$startDate, $endDate])
      ->join('email_logs', 'email_events.email_log_id', '=', 'email_logs.id')
      ->selectRaw('COUNT(DISTINCT email_logs.email) as total')
      ->value('total');
  }

  /**
   * Prepare chart data
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
        Log::info($event, [$event => $count]);
        if (isset($chartData[$date][$event])) {
          $chartData[$date][$event] = $count;
        }
      }
    }

    return $chartData;
  }

  /**
   * Generate date range array
   */
  private function generateDateRange($startDate, $endDate)
  {
    $dates = [];
    $currentDate = Carbon::parse($startDate)->startOfDay();
    $endDate = Carbon::parse($endDate)->endOfDay();

    while ($currentDate <= $endDate) {
      $dates[] = $currentDate->format('Y-m-d');
      $currentDate->addDay();
    }

    return $dates;
  }
}
