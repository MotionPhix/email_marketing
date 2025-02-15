<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignStats extends Model
{
  protected $fillable = [
    'campaign_id',
    'recipients_count',
    'delivered_count',
    'opened_count',
    'clicked_count',
    'bounced_count',
    'complained_count',
    'unsubscribed_count',
  ];

  public function campaign()
  {
    return $this->belongsTo(Campaign::class);
  }
}
