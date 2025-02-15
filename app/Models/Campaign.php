<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'name',
    'subject',
    'from_name',
    'from_email',
    'reply_to',
    'content',
    'template_id',
    'status',
    'scheduled_at',
    'sent_at',
    'settings',
    'recipients',
  ];

  protected $casts = [
    'settings' => 'json',
    'recipients' => 'json',
    'scheduled_at' => 'datetime',
    'sent_at' => 'datetime',
  ];

  const STATUS_DRAFT = 'draft';
  const STATUS_SCHEDULED = 'scheduled';
  const STATUS_SENDING = 'sending';
  const STATUS_SENT = 'sent';
  const STATUS_FAILED = 'failed';

  public function template()
  {
    return $this->belongsTo(EmailTemplate::class);
  }

  public function events()
  {
    return $this->hasMany(CampaignEvent::class);
  }

  public function stats()
  {
    return $this->hasOne(CampaignStats::class);
  }
}
