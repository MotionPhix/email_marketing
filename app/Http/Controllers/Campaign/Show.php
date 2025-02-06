<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Show extends Controller
{
  public function __invoke(Request $request, Campaign $campaign)
  {
    // Get date range from request or default to last 7 days
    $endDate = Carbon::now();
    $startDate = $request->get('start_date')
      ? Carbon::parse($request->get('start_date'))
      : $endDate->copy()->subDays(7);

    // Get detailed campaign statistics
    $stats = $campaign->getDetailedStats($startDate, $endDate);

    // Calculate rates
    $sentCount = $campaign->getSentCount();

    $rates = [
      'open_rate' => $sentCount > 0
        ? round(($stats['opened'] / $sentCount) * 100, 2)
        : 0,
      'click_rate' => $sentCount > 0
        ? round(($stats['clicked'] / $sentCount) * 100, 2)
        : 0,
      'bounce_rate' => $sentCount > 0
        ? round(($stats['bounced'] / $sentCount) * 100, 2)
        : 0,
      'unsubscribe_rate' => $sentCount > 0
        ? round(($stats['unsubscribed'] / $sentCount) * 100, 2)
        : 0,
    ];

    // Get email clients breakdown from opens
    $emailClients = $campaign->emailLogs()
      ->whereHas('events', function ($query) {
        $query->where('event', 'open');
      })
      ->with(['events' => function ($query) {
        $query->where('event', 'open')
          ->select('email_log_id', 'user_agent')
          ->distinct();
      }])
      ->get()
      ->flatMap(fn ($log) => $log->events)
      ->groupBy(function ($event) {
        // Basic email client detection - can be enhanced with a proper user agent parser
        $ua = strtolower($event->user_agent);
        if (str_contains($ua, 'outlook')) return 'Outlook';
        if (str_contains($ua, 'gmail')) return 'Gmail';
        if (str_contains($ua, 'apple')) return 'Apple Mail';
        if (str_contains($ua, 'thunderbird')) return 'Thunderbird';
        return 'Other';
      })
      ->map(fn ($group) => $group->count())
      ->sortDesc();

    // Get geographic distribution from opens and clicks
    $locations = $campaign->emailLogs()
      ->whereHas('events', function ($query) {
        $query->whereIn('event', ['open', 'click'])
          ->whereNotNull('ip');
      })
      ->with(['events' => function ($query) {
        $query->whereIn('event', ['open', 'click'])
          ->whereNotNull('ip')
          ->select('email_log_id', 'ip')
          ->distinct();
      }])
      ->get()
      ->flatMap(fn ($log) => $log->events)
      ->map(function ($event) {
        // You might want to use a proper IP geolocation service here
        // For now, we'll just group by IP
        return [
          'ip' => $event->ip,
          'country' => 'Unknown' // Replace with actual geolocation
        ];
      })
      ->groupBy('country')
      ->map(fn ($group) => $group->count())
      ->sortDesc();

    // Get click distribution
    $clickDistribution = $campaign->emailLogs()
      ->whereHas('events', function ($query) {
        $query->where('event', 'click')
          ->whereNotNull('url');
      })
      ->with(['events' => function ($query) {
        $query->where('event', 'click')
          ->whereNotNull('url')
          ->select('email_log_id', 'url', 'timestamp');
      }])
      ->get()
      ->flatMap(fn ($log) => $log->events)
      ->groupBy('url')
      ->map(fn ($group) => [
        'count' => $group->count(),
        'last_clicked' => $group->max('timestamp'),
      ])
      ->sortByDesc('count');

    // Get engagement timeline
    $timeline = $stats['events']
      ->groupBy(fn ($event) => Carbon::parse($event['timestamp'])->format('Y-m-d'))
      ->map(fn ($group) => [
        'opens' => $group->where('type', 'open')->count(),
        'clicks' => $group->where('type', 'click')->count(),
        'bounces' => $group->where('type', 'bounce')->count(),
        'unsubscribes' => $group->where('type', 'unsubscribe')->count(),
      ]);

    return Inertia::render('Campaigns/Show', [
      'campaign' => $campaign->load(['template', 'audience']),
      'stats' => [
        'summary' => [
          'total_recipients' => $campaign->getTotalRecipients(),
          'sent' => $sentCount,
          'opened' => $stats['opened'],
          'clicked' => $stats['clicked'],
          'bounced' => $stats['bounced'],
          'spam_reports' => $stats['spam_reports'],
          'unsubscribed' => $stats['unsubscribed'],
        ],
        'rates' => $rates,
        'email_clients' => $emailClients,
        'locations' => $locations,
        'clicks' => $clickDistribution,
        'timeline' => $timeline,
      ],
      'filters' => [
        'start_date' => $startDate->format('Y-m-d'),
        'end_date' => $endDate->format('Y-m-d'),
      ],
      'recent_events' => $stats['events']->take(50)->values(),
    ]);
  }
}
