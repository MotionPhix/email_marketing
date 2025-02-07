<?php

namespace App\Modules\Campaigns\Jobs;

use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Campaigns\Models\CampaignQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCampaignQueue implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public $tries = 3;
  public $timeout = 3600; // 1 hour

  public function __construct(
    protected Campaign $campaign
  ) {}

  public function handle(): void
  {
    if (!$this->campaign->isScheduled() && !$this->campaign->isDraft()) {
      return;
    }

    try {
      // Update campaign status
      $this->campaign->update([
        'status' => Campaign::STATUS_SENDING,
        'started_at' => now(),
      ]);

      // Get all recipients for this campaign
      $recipients = $this->campaign->recipients()
        ->whereDoesntHave('events', function ($query) {
          $query->where('campaign_id', $this->campaign->id);
        })
        ->get();

      // Create queue items for each recipient
      foreach ($recipients as $recipient) {
        CampaignQueue::create([
          'campaign_id' => $this->campaign->id,
          'recipient_id' => $recipient->id,
          'status' => CampaignQueue::STATUS_PENDING,
        ]);
      }

      // Process queue items in chunks to avoid memory issues
      CampaignQueue::where('campaign_id', $this->campaign->id)
        ->where('status', CampaignQueue::STATUS_PENDING)
        ->chunkById(100, function ($queueItems) {
          foreach ($queueItems as $queueItem) {
            SendCampaignEmail::dispatch($queueItem);
          }
        });

    } catch (\Exception $e) {
      Log::error('Campaign processing failed', [
        'campaign_id' => $this->campaign->id,
        'error' => $e->getMessage(),
      ]);

      $this->campaign->update([
        'status' => Campaign::STATUS_FAILED,
      ]);

      throw $e;
    }
  }
}
