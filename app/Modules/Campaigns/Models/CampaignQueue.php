<?php

namespace App\Modules\Campaigns\Models;

use App\Modules\Campaigns\Models\Recipient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignQueue extends Model
{
  protected $fillable = [
    'campaign_id',
    'recipient_id',
    'status',
    'error_message',
    'scheduled_at',
    'sent_at',
  ];

  protected $casts = [
    'scheduled_at' => 'datetime',
    'sent_at' => 'datetime',
  ];

  const STATUS_PENDING = 'pending';
  const STATUS_PROCESSING = 'processing';
  const STATUS_SENT = 'sent';
  const STATUS_FAILED = 'failed';

  public function campaign(): BelongsTo
  {
    return $this->belongsTo(Campaign::class);
  }

  public function recipient(): BelongsTo
  {
    return $this->belongsTo(Recipient::class);
  }
}
