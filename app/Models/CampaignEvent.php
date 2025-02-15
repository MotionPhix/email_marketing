<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignEvent extends Model
{
  protected $fillable = [
    'campaign_id',
    'subscriber_id',
    'type',
    'metadata',
  ];

  protected $casts = [
    'metadata' => 'json',
  ];

  const TYPE_SENT = 'sent';
  const TYPE_DELIVERED = 'delivered';
  const TYPE_OPENED = 'opened';
  const TYPE_CLICKED = 'clicked';
  const TYPE_BOUNCED = 'bounced';
  const TYPE_COMPLAINED = 'complained';
  const TYPE_UNSUBSCRIBED = 'unsubscribed';

  public function campaign()
  {
    return $this->belongsTo(Campaign::class);
  }

  public function subscriber()
  {
    return $this->belongsTo(Subscriber::class);
  }
}
