<?php

namespace App\Http\Controllers;

use App\Events\CampaignStatsUpdated;
use App\Models\Campaign;
use App\Models\EmailEvent;
use App\Models\EmailLog;
use App\Models\CampaignOpen;
use App\Models\CampaignClick;
use App\Models\CampaignUnsubscribe;
use App\Services\Campaign\CampaignStatsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Hook extends Controller
{
  public function __construct(protected CampaignStatsService $statsService)
  {
  }

  public function __invoke(Request $request)
  {
    Log::info('Webhook received:', $request->all());

    $events = $request->all();
    $processedCampaigns = [];

    foreach ($events as $event) {
      // Create or update EmailLog
      $emailLog = $this->processEmailLog($event);

      // Create EmailEvent
      $this->processEmailEvent($emailLog, $event);

      // Update high-level stats
      $this->updateCampaignStats($event);

      // Track unique campaigns
      $campaignUuid = $event['campaign_uuid'] ?? $event['unique_args']['campaign_id'] ?? null;
      if ($campaignUuid && !in_array($campaignUuid, $processedCampaigns)) {
        $processedCampaigns[] = $campaignUuid;
      }
    }

    // Broadcast updates
    $this->broadcastUpdates($processedCampaigns);

    return response()->json(['status' => 'success']);
  }

  protected function processEmailLog(array $event): EmailLog
  {
    return EmailLog::firstOrCreate(
      ['sg_message_id' => $event['sg_message_id']],
      [
        'campaign_uuid' => $event['campaign_uuid'] ?? $event['unique_args']['campaign_id'] ?? null,
        'email' => $event['email'],
        'user_uuid' => $event['user_uuid'] ?? null
      ]
    );
  }

  protected function processEmailEvent(EmailLog $emailLog, array $event): void
  {
    EmailEvent::create([
      'email_log_id' => $emailLog->id,
      'event' => $event['event'],
      'ip' => $event['ip'] ?? null,
      'user_agent' => $event['useragent'] ?? null,
      'url' => $event['url'] ?? null,
      'reason' => $event['reason'] ?? null,
      'status' => $event['status'] ?? null,
      'timestamp' => Carbon::createFromTimestamp($event['timestamp'])
    ]);
  }

  protected function updateCampaignStats(array $event): void
  {
    $campaign = Campaign::where('uuid', $event['campaign_uuid'] ?? $event['unique_args']['campaign_id'] ?? null)->first();
    if (!$campaign) return;

    $recipient = $campaign->recipients()->where('email', $event['email'])->first();
    if (!$recipient) return;

    switch ($event['event']) {
      case 'open':
        CampaignOpen::firstOrCreate([
          'campaign_id' => $campaign->id,
          'recipient_id' => $recipient->id,
          'ip_address' => $event['ip'] ?? null,
          'user_agent' => $event['useragent'] ?? null
        ]);
        break;

      case 'click':
        CampaignClick::firstOrCreate([
          'campaign_id' => $campaign->id,
          'recipient_id' => $recipient->id,
          'url' => $event['url'],
          'ip_address' => $event['ip'] ?? null,
          'user_agent' => $event['useragent'] ?? null
        ]);
        break;

      case 'unsubscribe':
        CampaignUnsubscribe::firstOrCreate([
          'campaign_id' => $campaign->id,
          'recipient_id' => $recipient->id,
          'reason' => $event['reason'] ?? null,
          'ip_address' => $event['ip'] ?? null,
          'user_agent' => $event['useragent'] ?? null
        ]);
        $recipient->update(['is_subscribed' => false]);
        break;
    }
  }

  protected function broadcastUpdates(array $campaignUuids): void
  {
    foreach ($campaignUuids as $campaignUuid) {
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
  }
}
