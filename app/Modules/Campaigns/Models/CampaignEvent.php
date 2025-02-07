<?php

namespace App\Modules\Campaigns\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignEvent extends Model
{
  protected $fillable = [
    'campaign_id',
    'email',
    'type',
    'metadata',
  ];

  protected $casts = [
    'metadata' => 'array',
  ];

  const TYPE_SENT = 'sent';
  const TYPE_OPENED = 'opened';
  const TYPE_CLICKED = 'clicked';
  const TYPE_BOUNCED = 'bounced';
  const TYPE_COMPLAINED = 'complained';

  public function campaign(): BelongsTo
  {
    return $this->belongsTo(Campaign::class);
  }
}
