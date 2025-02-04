<?php

namespace App\Events;

use App\Models\Campaign;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class CampaignScheduled
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   */
  public function __construct(
    public Campaign $campaign
  ) {}

  /**
   * Get the channels the event should broadcast on.
   *
   * @return array<int, \Illuminate\Broadcasting\Channel>
   */
  public function broadcastOn(): array
  {
    return [];
  }

  /**
   * Get the data to broadcast.
   *
   * @return array<string, mixed>
   */
  public function broadcastWith(): array
  {
    return [
      'campaign_uuid' => $this->campaign->uuid,
      'scheduled_at' => $this->campaign->scheduled_at?->toDateTimeString(),
      'end_date' => $this->campaign->end_date?->toDateTimeString(),
      'frequency' => $this->campaign->frequency,
    ];
  }
}
