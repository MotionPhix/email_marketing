<?php

namespace App\Events;

use App\Models\Campaign;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class CampaignScheduleCancelled
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
      'cancelled_at' => now()->toDateTimeString(),
      'previous_schedule' => [
        'scheduled_at' => $this->campaign->getOriginal('scheduled_at'),
        'end_date' => $this->campaign->getOriginal('end_date'),
        'frequency' => $this->campaign->frequency,
      ]
    ];
  }
}
