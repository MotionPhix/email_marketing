<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Request;

class Hook extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    $events = $request->all();

    \Log::error($events);

    foreach ($events as $event) {

      dd($event);

      $status = $this->mapStatus($event['event']);
      $campaignId = $event['custom_args']['campaign_id'] ?? null;

      EmailLog::updateOrCreate(
        [
          'message_id' => $event['sg_message_id'],
        ],
        [
          'status' => $status,
          'useragent' => $event['useragent'] ?? null,
          'recipient_email' => $event['email'],
          'category' => $event['category'][0] ?: null,
          'timestamp' => $event['timestamp'],
          'campaign_id' => $campaignId,
        ]
      );

    }

    return response()->json(['status' => 'success']);
  }

  private function mapStatus($event): string
  {
    return match ($event) {
      'dropped' => 'dropped',
      'delivered' => 'delivered',
      'bounce' => 'bounce',
      'failed' => 'failed',
      'group_unsubscribe' => 'group_unsubscribe',
      'group_resubscribe' => 'group_resubscribe',
      'deferred' => 'deferred',
      'spamreport' => 'spamreport',
      'unsubscribe' => 'unsubscribe',
      'open' => 'open',
      'click' => 'click',
      default => 'processed',
    };
  }
}
