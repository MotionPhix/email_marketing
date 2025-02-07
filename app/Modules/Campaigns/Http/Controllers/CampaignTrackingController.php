<?php

namespace App\Modules\Campaigns\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Campaigns\Models\EmailEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CampaignTrackingController extends Controller
{
  public function trackOpen(Request $request, Campaign $campaign, string $recipient): Response
  {
    EmailEvent::create([
      'campaign_id' => $campaign->id,
      'recipient_id' => $recipient,
      'event_type' => EmailEvent::EVENT_OPENED,
      'ip_address' => $request->ip(),
      'user_agent' => $request->userAgent(),
    ]);

    $campaign->increment('opened_count');

    return response(
      base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'),
      200,
      [
        'Content-Type' => 'image/gif',
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0'
      ]
    );
  }

  public function trackClick(Request $request, Campaign $campaign, string $recipient, string $url)
  {
    EmailEvent::create([
      'campaign_id' => $campaign->id,
      'recipient_id' => $recipient,
      'event_type' => EmailEvent::EVENT_CLICKED,
      'metadata' => ['url' => base64_decode($url)],
      'ip_address' => $request->ip(),
      'user_agent' => $request->userAgent(),
    ]);

    $campaign->increment('clicked_count');

    return redirect()->away(base64_decode($url));
  }
}
