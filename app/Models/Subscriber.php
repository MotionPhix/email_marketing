<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscriber extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'email',
    'first_name',
    'last_name',
    'company',
    'status',
    'metadata',
    'unsubscribed_at',
  ];

  protected $casts = [
    'metadata' => 'json',
    'unsubscribed_at' => 'datetime',
  ];

  const STATUS_SUBSCRIBED = 'subscribed';
  const STATUS_UNSUBSCRIBED = 'unsubscribed';
  const STATUS_BOUNCED = 'bounced';
  const STATUS_COMPLAINED = 'complained';

  public function campaignEvents()
  {
    return $this->hasMany(CampaignEvent::class);
  }

  public function isSubscribed()
  {
    return $this->status === self::STATUS_SUBSCRIBED;
  }

  public function fullName()
  {
    return trim($this->first_name . ' ' . $this->last_name);
  }
}
