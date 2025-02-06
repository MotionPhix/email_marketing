<?php

namespace App\Models;

use App\Traits\BootUuid;
use Illuminate\Database\Eloquent\Model;

class CampaignUnsubscribe extends Model
{
  use BootUuid;

  protected $fillable = [
    'campaign_id',
    'recipient_id',
    'reason',
    'ip_address',
    'user_agent'
  ];

  public function campaign()
  {
    return $this->belongsTo(Campaign::class);
  }

  public function recipient()
  {
    return $this->belongsTo(Recipient::class);
  }
}
