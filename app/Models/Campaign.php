<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
    'team_id',
    'user_id',
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

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function subscribers()
  {
    return $this->belongsToMany(Subscriber::class, 'campaign_events')
      ->withPivot('type', 'metadata')
      ->withTimestamps();
  }

  public function team()
  {
    return $this->belongsTo(Team::class);
  }

  public function events()
  {
    return $this->hasMany(CampaignEvent::class);
  }

  public function stats()
  {
    return $this->hasOne(CampaignStats::class);
  }

  public function campaignEvent(): HasMany
  {
    return $this->hasMany(CampaignEvent::class);
  }

  public function scopeSearch($query, string $search)
  {
    $query->where(function ($q) use ($search) {
      $q->where('name', 'like', "%{$search}%")
        ->orWhere('subject', 'like', "%{$search}%")
        ->orWhere('from_name', 'like', "%{$search}%")
        ->orWhere('from_email', 'like', "%{$search}%");
    });
  }

  public function scopeWithStats($query)
  {
    $query->addSelect([
      'recipients_count' => CampaignStats::select('recipients_count')
        ->whereColumn('campaign_id', 'campaigns.id')
        ->latest()
        ->take(1)
    ]);
  }

  public function scopeStatus($query, string $status)
  {
    $query->where('status', $status);
  }

  public function scopeDateRange($query, $startDate, $endDate)
  {
    $query->when($startDate, function ($q, $date) {
      $q->whereDate('created_at', '>=', $date);
    })->when($endDate, function ($q, $date) {
      $q->whereDate('created_at', '<=', $date);
    });
  }

  public function scopeForTeam($query, $team)
  {
    $query->where('team_id', $team->id);
  }
}
