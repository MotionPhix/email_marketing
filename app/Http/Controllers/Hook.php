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
      $status = $this->mapStatus($event['event']);  // Map status to one of the enum values
      $campaignId = $event['custom_args']['campaign_id'] ?? null; // Capture campaign ID

      EmailLog::updateOrCreate(
        [
          'message_id' => $event['sg_message_id'],
        ],
        [
          'status' => $status,
          'message_id' => $event['sg_message_id'],
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

  private function mapStatus($event)
  {
    // Map SendGrid event to the predefined enum values
    switch ($event) {
      case 'processed':
        return 'processed'; // or another valid value based on your use case
      case 'delivered':
        return 'sent';
      case 'bounce':
        return 'bounced';
      case 'failed':
        return 'failed';
      default:
        return 'pending'; // Default value if event is unknown
    }
  }
}
