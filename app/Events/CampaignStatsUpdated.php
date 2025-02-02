<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CampaignStatsUpdated implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public $campaignId;
  public $userId;
  public $stats;
  public $chartData;

  public function __construct($campaignId, $userId, $stats, $chartData)
  {
    $this->campaignId = $campaignId;
    $this->userId = $userId;
    $this->stats = $stats;
    $this->chartData = $chartData;
  }

  public function broadcastOn()
  {
    return new PrivateChannel("campaign.stats.{$this->userId}");
  }
}
