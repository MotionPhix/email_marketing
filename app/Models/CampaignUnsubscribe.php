<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignUnsubscribe extends Model
{
  protected $fillable = [
    'recipient_id',
    'campaign_id',
  ];

  /**
   * Get the recipient that unsubscribed.
   */
  public function recipient()
  {
    return $this->belongsTo(Recipient::class);
  }

  /**
   * Get the campaign that the recipient unsubscribed from.
   */
  public function campaign()
  {
    return $this->belongsTo(Campaign::class);
  }
}
