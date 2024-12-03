<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Recipient;

class Unsubscribe extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Campaign $campaign, Recipient $recipient)
  {
    // Check if already unsubscribed
    if (!$campaign->unsubscribes()->where('recipient_id', $recipient->id)->exists()) {
      $campaign->unsubscribes()->create(['recipient_id' => $recipient->id]);
    }

    return redirect()->route('campaigns.outed');
  }
}
