<?php

namespace App\Http\Controllers;

use App\Events\CampaignStatsUpdated;
use App\Models\Campaign;
use App\Models\EmailEvent;
use App\Models\EmailLog;
use App\Services\Campaign\CampaignStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SendGrid\EventWebhook\EventWebhook;
use SendGrid\EventWebhook\EventWebhookHeader;

class Hook extends Controller
{
  public function __construct(protected CampaignStatsService $statsService)
  {
  }

  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    // Log the raw incoming event data for debugging purposes
    Log::info('Webhook received:', $request->all());

    $events = $request->all();
    $processedCampaigns = [];

    // Transform event data for batch insertion
    $eventData = array_map(function ($event) {
      return [
        'email' => $event['email'] ?? null,
        'event' => $event['event'] ?? null,
        'campaign_uuid' => $event['campaign_uuid'] ?? $event['unique_args']['campaign_id'] ?? null,
        'user_uuid' => $event['user_uuid'] ?? null,
        'timestamp' => \Carbon\Carbon::createFromTimestamp($event['timestamp'])->toDateTimeString(),
        'ip' => $event['ip'] ?? null,
        'user_agent' => $event['useragent'] ?? null,
        'sg_message_id' => $event['sg_message_id'] ?? null,
        'url' => $event['url'] ?? null,
        'reason' => $event['reason'] ?? null,
        'status' => $event['status'] ?? null,
      ];
    }, $events);

    // Process each event
    foreach ($eventData as $data) {

      $emailLog = EmailLog::firstOrCreate(
        ['sg_message_id' => $data['sg_message_id']],
        [
          'campaign_uuid' => $data['campaign_uuid'],
          'email' => $data['email'],
          'user_uuid' => $data['user_uuid']
        ]
      );

      EmailEvent::insert([
        'ip' => $data['ip'],
        'event' => $data['event'],
        'email_log_id' => $emailLog->id,
        'timestamp' => $data['timestamp'],
        'user_agent' => $data['user_agent'],
        'reason' => $data['reason'],
        'status' => $data['status'],
        'url' => $data['url'],
      ]);

      // Track unique campaigns to update
      if (!in_array($data['campaign_uuid'], $processedCampaigns)) {
        $processedCampaigns[] = $data['campaign_uuid'];
      }
    }

    // Broadcast updates for each affected campaign
    foreach ($processedCampaigns as $campaignUuid) {
      $campaign = Campaign::with('emailLogs.events')
        ->where('uuid', $campaignUuid)
        ->first();

      if ($campaign) {
        $stats = $this->statsService->getStats(
          $campaignUuid,
          now()->subDays(7),
          now()
        );

        event(new CampaignStatsUpdated(
          $campaign->uuid,
          $campaign->user_uuid,
          $stats['stats'],
          $stats['chart']
        ));
      }
    }

    return response()->json(['status' => 'success']);
  }
}
