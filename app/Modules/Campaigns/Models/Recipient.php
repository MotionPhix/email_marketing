<?php

namespace App\Modules\Campaigns\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Recipient extends Model
{
  use SoftDeletes, HasUuids;

  protected $fillable = [
    'email',
    'name',
    'metadata',
    'subscribed',
    'unsubscribed_at',
  ];

  protected $casts = [
    'metadata' => 'array',
    'subscribed' => 'boolean',
    'unsubscribed_at' => 'datetime',
  ];

  public function campaigns(): BelongsToMany
  {
    return $this->belongsToMany(Campaign::class, 'campaign_recipients');
  }

  public function events(): HasMany
  {
    return $this->hasMany(EmailEvent::class);
  }
}
