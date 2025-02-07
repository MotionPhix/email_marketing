<?php

namespace App\Modules\Campaigns\Models;

use App\Modules\Campaigns\Models\Recipient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailEvent extends Model
{
  protected $fillable = [
    'campaign_id',
    'recipient_id',
    'event_type',
    'metadata',
    'ip_address',
    'user_agent',
  ];

  protected $casts = [
    'metadata' => 'json',
  ];

  const EVENT_OPENED = 'opened';
  const EVENT_CLICKED = 'clicked';
  const EVENT_BOUNCED = 'bounced';
  const EVENT_COMPLAINED = 'complained';
  const EVENT_DELIVERED = 'delivered';

  public function campaign(): BelongsTo
  {
    return $this->belongsTo(Campaign::class);
  }

  public function recipient(): BelongsTo
  {
    return $this->belongsTo(Recipient::class);
  }
}
