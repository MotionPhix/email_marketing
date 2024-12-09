<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\EmailEvent;
use App\Models\Recipient;
use Illuminate\Http\Request;

class Show extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Recipient $recipient)
  {
    // Get recipient stats
    $stats = EmailEvent::whereHas('emailLog', function ($query) use ($recipient) {
      $query->where('email', $recipient->email);
    })
      ->selectRaw('
      COUNT(DISTINCT email_log_id) as totalCampaigns,
      SUM(event = "open") as opened,
      SUM(event = "click") as clicked,
      SUM(event = "bounce") as bounced,
      SUM(event = "complaint") as complained,
      SUM(event = "delivered") as delivered
    ')
      ->first();

    $recentInteractions = EmailEvent::whereHas('emailLog', function ($query) use ($recipient) {
      $query->where('email', $recipient->email);
    })
      ->with('emailLog.campaign')
      ->orderBy('timestamp', 'desc')
      ->limit(5)
      ->get()
      ->map(function ($event) {
        $campaign = Campaign::where('uuid', $event->emailLog->campaign_uuid)->first();

        return [
          'campaign' => [
            'uuid' => $campaign->uuid,
            'title' => $campaign->title,
          ],
          'status' => $event->event,
          'date' => $event->timestamp->format('Y-m-d H:i'),
        ];
      });

    return Inertia('Recipients/Show', [
      'recipient' => $recipient->only(['uuid', 'name', 'email', 'audiences']),
      'stats' => [
        'totalCampaigns' => $stats->totalCampaigns ?? 0,
        'opened' => $stats->opened ?? 0,
        'clicked' => $stats->clicked ?? 0,
        'bounced' => $stats->bounced ?? 0,
        'complained' => $stats->complained ?? 0,
        'delivered' => $stats->delivered ?? 0,
        'recentInteractions' => $recentInteractions,
      ],
    ]);
  }
}
