<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingEvent extends Model
{
  protected $fillable = [
    'user_id',
    'campaign_id',
    'type',
    'email',
    'metadata',
    'occurred_at',
  ];

  protected $casts = [
    'metadata' => 'array',
    'occurred_at' => 'datetime',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function campaign(): BelongsTo
  {
    return $this->belongsTo(Campaign::class);
  }
}
