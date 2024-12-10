<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\EmailEvent;
use App\Models\EmailLog;
use App\Models\Recipient;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Show extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Recipient $recipient)
  {
    $totalEmailsSent = EmailLog::where('email', $recipient->email)->count();

    $totalCampaigns = EmailLog::where('email', $recipient->email)
      ->distinct()
      ->count('campaign_uuid');

    $totalAudiences = $recipient->audiences()->count();

    // Fetch recipient email stats grouped by event and date
    $eventStats = EmailEvent::whereHas('emailLog', function ($query) use ($recipient) {
      $query->where('email', $recipient->email);
    })
      ->selectRaw('event, COUNT(*) as total, DATE(timestamp) as date')
      ->groupBy('event', 'date')
      ->orderBy('date')
      ->get();

    // Fetch unique opens and clicks
//    $uniqueStats = EmailEvent::join('email_logs', 'email_events.email_log_id', '=', 'email_logs.id')
//      ->where('email_logs.email', $recipient->email)
//      ->whereIn('email_events.event', ['open', 'click'])
//      ->selectRaw('email_events.event, COUNT(DISTINCT email_logs.email) as total')
//      ->groupBy('email_events.event')
//      ->pluck('total', 'event');

    // Unique stats (opens, clicks) with percentages
    $uniqueStats = EmailEvent::join('email_logs', 'email_events.email_log_id', '=', 'email_logs.id')
      ->where('email_logs.email', $recipient->email)
      ->whereIn('email_events.event', ['open', 'click'])
      ->selectRaw(
        'email_events.event,
         COUNT(DISTINCT email_logs.id) as total,
         (COUNT(DISTINCT email_logs.id) / ?) * 100 as percentage',
        [$totalEmailsSent]
      )
      ->groupBy('email_events.event')
      ->get();

    // Generate summary stats by summing up totals for each event
    $summaryStats = $eventStats->groupBy('event')->mapWithKeys(function ($group, $event) {
      return [$event => $group->sum('total')];
    });

    // Fill missing events with 0 to ensure consistent structure
    $summaryStats = array_merge([
      'open' => 0,
      'click' => 0,
      'bounce' => 0,
      'unsubscribe' => 0,
      'delivered' => 0,
      'deferred' => 0,
      'processed' => 0,
      'spamreport' => 0,
    ], $summaryStats->toArray());

    // Calculate total emails for percentages
    $totalEmails = array_sum($summaryStats);

    // Add percentages to stats
    $summaryStatsWithPercentages = collect($summaryStats)->mapWithKeys(function ($count, $event) use ($totalEmails) {
      return [
        $event => [
          'count' => $count,
          'percentage' => $totalEmails > 0 ? round(($count / $totalEmails) * 100, 2) : 0,
        ]
      ];
    });

    // Add unique opens and clicks
    $summaryStatsWithPercentages['unique_opens'] = [
      'count' => $uniqueStats['open'] ?? 0,
      'percentage' => $totalEmails > 0 ? round((($uniqueStats['open'] ?? 0) / $totalEmails) * 100, 2) : 0,
    ];
    $summaryStatsWithPercentages['unique_clicks'] = [
      'count' => $uniqueStats['click'] ?? 0,
      'percentage' => $totalEmails > 0 ? round((($uniqueStats['click'] ?? 0) / $totalEmails) * 100, 2) : 0,
    ];

    // Prepare chart data grouped by date
    $chartData = $eventStats->groupBy('date')->map(function ($dayEvents) {
      return $dayEvents->keyBy('event')->map(fn($event) => $event->total);
    });

    // Recent interactions with campaigns
    $recentInteractions = EmailEvent::join('email_logs', 'email_events.email_log_id', '=', 'email_logs.id')
      ->join('campaigns', 'email_logs.campaign_uuid', '=', 'campaigns.uuid') // Assuming the campaigns table has a `uuid` column
      ->where('email_logs.email', $recipient->email)
      ->whereIn('email_events.event', ['delivered', 'click', 'open', 'bounce', 'unsubscribe', 'spamreport'])
      ->selectRaw(
        'email_logs.campaign_uuid,
         campaigns.title as campaign_title,
         email_events.event as status,
         DATE_FORMAT(email_events.timestamp, "%d-%m-%Y %H:%i:%s") as date'
      )
      ->distinct()
      ->orderBy('date')
      ->get()
      ->groupBy('campaign_uuid')
      ->map(function ($campaignEvents) {
        $firstEvent = $campaignEvents->first();
        return [
          'campaign' => [
            'uuid' => $firstEvent->campaign_uuid,
            'title' => $firstEvent->campaign_title ?? 'Unknown Campaign',
            'activity' => $campaignEvents->map(function ($event) {
              return [
                'status' => $event->status,
                'date' => Carbon::createFromTimeString($event->date)->format('j M, Y h:i:s'),
              ];
            })->values()
          ]
        ];
      })->values(); // Reset the indices

      /*->map(function ($interaction) {
        return [
          'campaign' => [
            'uuid' => $interaction->campaign_uuid,
            'title' => $interaction->campaign_title ?? 'Unknown Campaign',
          ],
          'status' => $interaction->status,
          'date' => Carbon::createFromTimeString($interaction->date)->format('j M, Y h:i:s'),
        ];
      })*/

    return Inertia('Recipients/Show', [
      'recipient' => $recipient,
      'stats' => $summaryStatsWithPercentages,
      'totalEmailsSent' => $totalEmailsSent,
      'recentInteractions' => $recentInteractions,
      'totalCampaigns' => $totalCampaigns,
      'totalAudiences' => $totalAudiences,
      'chartData' => $chartData,
    ]);

  }
}
