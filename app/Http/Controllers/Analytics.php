<?php

namespace App\Http\Controllers;

use App\Models\EmailEvent;
use App\Models\Campaign;
use App\Models\EmailLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Analytics extends Controller
{
  public function __invoke(Request $request)
  {
    /*// Total stats
    $totalEmailsSent = EmailEvent::where('event', 'processed')->distinct('email_log_id')->count('email_log_id');
    $totalRecipients = EmailLog::distinct('email')->count('email');
    $totalDelivered = EmailEvent::where('event', 'delivered')->distinct('email_log_id')->count('email_log_id');
    $totalCampaigns = Campaign::count();

    // Summary stats grouped by event type (e.g., delivered, opened, clicked, etc.)
    $summaryStats = EmailEvent::selectRaw('event, COUNT(*) as total')
      ->groupBy('event')
      ->pluck('total', 'event');

    // Chart data grouped by date for delivered emails
    $chartData = EmailEvent::where('event', 'delivered')
      ->selectRaw('DATE(timestamp) as date, COUNT(*) as total')
      ->groupBy('date')
      ->orderBy('date', 'asc')
      ->pluck('total', 'date');

    // Latest activity feed (limit to recent 10 events)
    $eventFeed = EmailEvent::latest('timestamp')
      ->with('emailLog:id,email')
      ->take(10)
      ->get(['event', 'reason', 'email_log_id', 'timestamp']);

    // Return response to Inertia
    return inertia('Dashboard', [
      'stats' => [
        'totalEmailsSent' => $totalEmailsSent,
        'totalRecipients' => $totalRecipients,
        'totalCampaigns' => $totalCampaigns,
        'totalDelivered' => $totalDelivered, // Added for clarity
        'summary' => $summaryStats
      ],
      'chartData' => $chartData,
      'eventFeed' => $eventFeed,
      'currentTime' => now()->format('l, jS F Y h:i A'),
    ]);*/

    // Filter by date range if provided
    $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
    $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

    // Total stats
    $totalEmailsSent = EmailEvent::whereBetween('timestamp', [$startDate, $endDate])
      ->where('event', 'processed')
      ->distinct('email_log_id')
      ->count('email_log_id');

    $totalRecipients = EmailLog::whereBetween('created_at', [$startDate, $endDate])
      ->distinct('email')
      ->count('email');

    $totalDelivered = EmailEvent::whereBetween('timestamp', [$startDate, $endDate])
      ->where('event', 'delivered')
      ->distinct('email_log_id')
      ->count('email_log_id');

    $totalCampaigns = Campaign::whereBetween('created_at', [$startDate, $endDate])->count();

    // Summary stats grouped by distinct event type and email_log_id (e.g., delivered, opened, clicked, etc.)
    $summaryStats = EmailEvent::whereBetween('timestamp', [$startDate, $endDate])
      ->selectRaw('event, COUNT(DISTINCT email_log_id) as total')
      ->groupBy('event')
      ->pluck('total', 'event');

    // Event rates (CTR, Open Rate, etc.)
    $eventRates = EmailEvent::whereBetween('timestamp', [$startDate, $endDate])
      ->selectRaw('(
        (COUNT(DISTINCT CASE WHEN event = "click" THEN email_log_id END) * 100.0) /
          NULLIF(COUNT(DISTINCT CASE WHEN event = "delivered" THEN email_log_id END), 0)
        ) as ctr, (
          (COUNT(DISTINCT CASE WHEN event = "open" THEN email_log_id END) * 100.0) /
          NULLIF(COUNT(DISTINCT CASE WHEN event = "delivered" THEN email_log_id END), 0)
        ) as open_rate, (
          (COUNT(DISTINCT CASE WHEN event = "bounce" THEN email_log_id END) * 100.0) /
          NULLIF(COUNT(DISTINCT CASE WHEN event = "processed" THEN email_log_id END), 0)
        ) as bounce_rate, (
          (COUNT(DISTINCT CASE WHEN event = "unsubscribe" THEN email_log_id END) * 100.0) /
          NULLIF(COUNT(DISTINCT CASE WHEN event = "processed" THEN email_log_id END), 0)
        ) as unsubscribe_rate, (
          (COUNT(DISTINCT CASE WHEN event = "delivered" THEN email_log_id END) * 100.0) /
          NULLIF(COUNT(DISTINCT CASE WHEN event = "processed" THEN email_log_id END), 0)
        ) as delivery_rate
      ')
      ->first();

    // Chart data grouped by date for all key event types
    $chartData = EmailEvent::whereBetween('timestamp', [$startDate, $endDate])
      ->selectRaw('DATE(timestamp) as date, event, COUNT(DISTINCT email_log_id) as total')
      ->groupBy('date', 'event')
      ->orderBy('date', 'asc')
      ->get()
      ->groupBy('event')
      ->map(function ($group) {
        return $group->pluck('total', 'date');
      });

    // Latest activity feed (limit to recent 10 events)
    $eventFeed = EmailEvent::whereBetween('timestamp', [$startDate, $endDate])
      ->with('emailLog:id,email')
      ->latest('timestamp')
      ->take(10)
      ->get(['event', 'email_log_id', 'timestamp']);

    // Top 5 campaigns by click count
    $topCampaigns = Campaign::withCount([
      'emailLogs as total_sent' => function ($query) {
        $query->whereHas('events', function ($query) {
          $query->where('event', 'processed');
        });
      },
      'emailLogs as total_clicks' => function ($query) {
        $query->whereHas('events', function ($query) {
          $query->where('event', 'click');
        });
      }
    ])
      ->whereBetween('created_at', [$startDate, $endDate])
      ->orderByDesc('total_clicks')
      ->limit(5)
      ->get();

    return inertia('Dashboard', [
      'stats' => [
        'totalEmailsSent' => $totalEmailsSent,
        'totalRecipients' => $totalRecipients,
        'totalCampaigns' => $totalCampaigns,
        'summary' => $summaryStats
      ],
      'eventRates' => $eventRates,
      'chartData' => $chartData,
      'eventFeed' => $eventFeed,
      'topCampaigns' => $topCampaigns,
      'currentTime' => now()->format('l, jS F Y h:i A'),
    ]);

  }
}
