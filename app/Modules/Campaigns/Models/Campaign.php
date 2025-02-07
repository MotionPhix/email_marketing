<?php

namespace App\Modules\Campaigns\Models;

use App\Models\User;
use App\Modules\Lists\Models\MailingList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Campaign extends Model
{
  use SoftDeletes, HasUuids;

  protected $fillable = [
    'user_id',
    'name',
    'subject',
    'content',
    'template_data',
    'from_name',
    'from_email',
    'reply_to',
    'scheduled_at',
    'started_at',
    'completed_at',
    'status',
    'total_recipients',
    'sent_count',
    'opened_count',
    'clicked_count',
    'bounced_count',
    'complained_count',
  ];

  protected $casts = [
    'template_data' => 'array',
    'scheduled_at' => 'datetime',
    'started_at' => 'datetime',
    'completed_at' => 'datetime',
  ];

  const STATUS_DRAFT = 'draft';
  const STATUS_SCHEDULED = 'scheduled';
  const STATUS_SENDING = 'sending';
  const STATUS_SENT = 'sent';
  const STATUS_FAILED = 'failed';

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function lists(): BelongsToMany
  {
    return $this->belongsToMany(MailingList::class, 'campaign_lists', 'campaign_id', 'list_id')
      ->withTimestamps();
  }

  public function events(): HasMany
  {
    return $this->hasMany(CampaignEvent::class);
  }

  public function isDraft(): bool
  {
    return $this->status === self::STATUS_DRAFT;
  }

  public function isScheduled(): bool
  {
    return $this->status === self::STATUS_SCHEDULED;
  }

  public function isSending(): bool
  {
    return $this->status === self::STATUS_SENDING;
  }

  public function isSent(): bool
  {
    return $this->status === self::STATUS_SENT;
  }

  public function isFailed(): bool
  {
    return $this->status === self::STATUS_FAILED;
  }

  public function canBeEdited(): bool
  {
    return $this->isDraft() || $this->isScheduled();
  }

  public function getOpenRate(): float
  {
    if ($this->sent_count === 0) {
      return 0;
    }

    return round(($this->opened_count / $this->sent_count) * 100, 2);
  }

  public function getClickRate(): float
  {
    if ($this->sent_count === 0) {
      return 0;
    }

    return round(($this->clicked_count / $this->sent_count) * 100, 2);
  }

  public function getBounceRate(): float
  {
    if ($this->sent_count === 0) {
      return 0;
    }

    return round(($this->bounced_count / $this->sent_count) * 100, 2);
  }

  public function getComplaintRate(): float
  {
    if ($this->sent_count === 0) {
      return 0;
    }

    return round(($this->complained_count / $this->sent_count) * 100, 2);
  }

  public function queueItems(): HasMany
  {
    return $this->hasMany(CampaignQueue::class);
  }

  public function emailEvents(): HasMany
  {
    return $this->hasMany(EmailEvent::class);
  }

  public function queue(array $recipientIds, ?Carbon $scheduleAt = null): void
  {
    $items = collect($recipientIds)->map(fn ($recipientId) => [
      'recipient_id' => $recipientId,
      'status' => CampaignQueue::STATUS_PENDING,
      'scheduled_at' => $scheduleAt,
      'created_at' => now(),
      'updated_at' => now(),
    ]);

    $this->queueItems()->insert($items->toArray());

    $this->queueItems()
      ->where('status', CampaignQueue::STATUS_PENDING)
      ->each(function (CampaignQueue $queueItem) {
        if ($queueItem->scheduled_at) {
          SendCampaignEmail::dispatch($queueItem)
            ->delay($queueItem->scheduled_at);
        } else {
          SendCampaignEmail::dispatch($queueItem);
        }
      });
  }

  public function getStats(): array
  {
    $queueStats = $this->queueItems()
      ->selectRaw('status, count(*) as count')
      ->groupBy('status')
      ->pluck('count', 'status')
      ->toArray();

    $eventStats = $this->emailEvents()
      ->selectRaw('event_type, count(*) as count')
      ->groupBy('event_type')
      ->pluck('count', 'event_type')
      ->toArray();

    $totalRecipients = array_sum($queueStats);
    $totalSent = $queueStats[CampaignQueue::STATUS_SENT] ?? 0;

    return [
      'total_recipients' => $totalRecipients,
      'sent' => $totalSent,
      'pending' => $queueStats[CampaignQueue::STATUS_PENDING] ?? 0,
      'failed' => $queueStats[CampaignQueue::STATUS_FAILED] ?? 0,
      'opened' => $eventStats[EmailEvent::EVENT_OPENED] ?? 0,
      'clicked' => $eventStats[EmailEvent::EVENT_CLICKED] ?? 0,
      'bounced' => $eventStats[EmailEvent::EVENT_BOUNCED] ?? 0,
      'complained' => $eventStats[EmailEvent::EVENT_COMPLAINED] ?? 0,
      'open_rate' => $totalSent ? round(($eventStats[EmailEvent::EVENT_OPENED] ?? 0) / $totalSent * 100, 2) : 0,
      'click_rate' => $totalSent ? round(($eventStats[EmailEvent::EVENT_CLICKED] ?? 0) / $totalSent * 100, 2) : 0,
    ];
  }
}
