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
    // Total stats
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
    ]);
  }
}
