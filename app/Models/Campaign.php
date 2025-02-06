<?php

namespace App\Models;

use App\Traits\BootUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
  use HasFactory, BootUuid;

  protected $fillable = [
    'title',
    'subject',
    'status',
    'step',
    'scheduled_at',
    'completed_at',
    'user_id',
    'audience_id',
    'template_id',
    'description',
    'track_opens' => 'boolean',
    'track_clicks' => 'boolean',
    'frequency',
    'send_at',
    'metadata'
  ];

  // Campaign statuses
  const STATUS_DRAFT = 'draft';
  const STATUS_SCHEDULED = 'scheduled';
  const STATUS_SENDING = 'sending';
  const STATUS_SENT = 'sent';
  const STATUS_FAILED = 'failed';
  const STATUS_CANCELLED = 'cancelled';

  // Add step constants
  public const STEP_DETAILS = 1;
  public const STEP_TEMPLATE = 2;
  public const STEP_AUDIENCE = 3;

  protected $casts = [
    'scheduled_at' => 'datetime',
    'sent_at' => 'datetime',
    'last_sent_at' => 'datetime',
    'completed_at' => 'datetime',
    'track_opens' => 'boolean',
    'track_clicks' => 'boolean',
    'metadata' => 'array',
  ];

  protected $attributes = [
    'step' => self::STEP_DETAILS, // Default to first step
  ];

  public static function getStatuses(): array
  {
    return [
      self::STATUS_DRAFT => 'Draft',
      self::STATUS_SCHEDULED => 'Scheduled',
      self::STATUS_SENDING => 'Sending',
      self::STATUS_SENT => 'Sent',
      self::STATUS_FAILED => 'Failed',
      self::STATUS_CANCELLED => 'Cancelled',
    ];
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function emailLogs()
  {
    return $this->hasMany(EmailLog::class, 'campaign_uuid', 'uuid');
  }

  public function audience()
  {
    return $this->belongsTo(Audience::class);
  }

  public function template()
  {
    return $this->belongsTo(Template::class);
  }

  // A campaign has many recipients
  public function recipients()
  {
    return $this->belongsToMany(Recipient::class, 'campaign_recipients');
  }

  public function formattedScheduledAt(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->getAttribute('scheduled_at')
        ? Carbon::parse($this->getAttribute('scheduled_at'))->format('D, d M, Y')
        : null,
    );
  }

  public function formattedEndDate(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->getAttribute('end_date')
        ? Carbon::parse($this->getAttribute('end_date'))->format('D, d M, Y')
        : null,
    );
  }

  // Add new step-related methods
  public function nextStep(): void
  {
    if ($this->step < self::STEP_AUDIENCE) {
      $this->increment('step');
    }
  }

  public function previousStep(): void
  {
    if ($this->step > self::STEP_DETAILS) {
      $this->decrement('step');
    }
  }

  public function setStep(int $step): void
  {
    if ($step >= self::STEP_DETAILS && $step <= self::STEP_AUDIENCE) {
      $this->update(['step' => $step]);
    }
  }

  protected static function booted()
  {
    static::creating(function ($campaign) {
      if (!$campaign->status) {
        $campaign->status = self::STATUS_DRAFT;
      }

      if (!$campaign->step) {
        $campaign->step = self::STEP_DETAILS;
      }
    });
  }

  public function opens()
  {
    return $this->hasMany(CampaignOpen::class);
  }

  public function clicks()
  {
    return $this->hasMany(CampaignClick::class);
  }

  public function unsubscribes()
  {
    return $this->hasMany(CampaignUnsubscribe::class);
  }

  // Scopes
  public function scopeDraft(Builder $query): Builder
  {
    return $query->where('status', self::STATUS_DRAFT);
  }

  public function scopeScheduled(Builder $query): Builder
  {
    return $query->where('status', self::STATUS_SCHEDULED);
  }

  public function scopeSending(Builder $query): Builder
  {
    return $query->where('status', self::STATUS_SENDING);
  }

  public function scopeSent(Builder $query): Builder
  {
    return $query->where('status', self::STATUS_SENT);
  }

  public function scopeFailed(Builder $query): Builder
  {
    return $query->where('status', self::STATUS_FAILED);
  }

  public function scopeCancelled(Builder $query): Builder
  {
    return $query->where('status', self::STATUS_CANCELLED);
  }

  public function scopePending(Builder $query): Builder
  {
    return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_SCHEDULED]);
  }

  public function scopeActive(Builder $query): Builder
  {
    return $query->whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_SENDING]);
  }

  public function scopeCompleted(Builder $query): Builder
  {
    return $query->whereIn('status', [self::STATUS_SENT, self::STATUS_FAILED, self::STATUS_CANCELLED]);
  }

  // Helper methods
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

  public function isCancelled(): bool
  {
    return $this->status === self::STATUS_CANCELLED;
  }

  public function isEditable(): bool
  {
    return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED]);
  }

  public function canBeSent(): bool
  {
    return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_FAILED])
      && $this->template_id
      && $this->audience_id;
  }

  public function canBeScheduled(): bool
  {
    return $this->canBeSent() && $this->user->canScheduleCampaigns();
  }

  // Stats and metrics
  public function getTotalRecipients(): int
  {
    return $this->recipients()->count();
  }

  public function getSentCount(): int
  {
    return $this->emailLogs()
      ->whereHas('events', function ($query) {
        $query->where('event', 'delivered');
      })
      ->count();
  }

  public function getOpenCount(): int
  {
    // Combine both tracking systems
    $webhookOpens = $this->emailLogs()
      ->whereHas('events', function ($query) {
        $query->where('event', 'open');
      })
      ->count();

    $pixelOpens = $this->opens()->count();

    return max($webhookOpens, $pixelOpens);
  }

  public function getClickCount(): int
  {
    // Combine both tracking systems
    $webhookClicks = $this->emailLogs()
      ->whereHas('events', function ($query) {
        $query->where('event', 'click');
      })
      ->count();

    $trackedClicks = $this->clicks()->count();

    return max($webhookClicks, $trackedClicks);
  }

  public function getFailedCount(): int
  {
    return $this->emailLogs()
      ->whereHas('events', function ($query) {
        $query->whereIn('event', ['bounce', 'dropped', 'failed']);
      })
      ->count();
  }

  public function getUnsubscribeCount(): int
  {
    // Combine both tracking systems
    $webhookUnsubscribes = $this->emailLogs()
      ->whereHas('events', function ($query) {
        $query->where('event', 'unsubscribe');
      })
      ->count();

    $trackedUnsubscribes = $this->unsubscribes()->count();

    return max($webhookUnsubscribes, $trackedUnsubscribes);
  }

  public function getOpenRate(): float
  {
    $sent = $this->getSentCount();
    return $sent > 0 ? ($this->getOpenCount() / $sent) * 100 : 0;
  }

  public function getClickRate(): float
  {
    $sent = $this->getSentCount();
    return $sent > 0 ? ($this->getClickCount() / $sent) * 100 : 0;
  }

  public function getFailureRate(): float
  {
    $total = $this->getTotalRecipients();
    return $total > 0 ? ($this->getFailedCount() / $total) * 100 : 0;
  }

  public function getUnsubscribeRate(): float
  {
    $sent = $this->getSentCount();
    return $sent > 0 ? ($this->getUnsubscribeCount() / $sent) * 100 : 0;
  }

  // Campaign processing
  public function schedule(Carbon $scheduledAt): bool
  {
    if (!$this->canBeScheduled()) {
      return false;
    }

    return $this->update([
      'status' => self::STATUS_SCHEDULED,
      'scheduled_at' => $scheduledAt
    ]);
  }

  public function cancel(): bool
  {
    if (!$this->isScheduled()) {
      return false;
    }

    return $this->update([
      'status' => self::STATUS_CANCELLED,
      'scheduled_at' => null
    ]);
  }

  public function markAsSending(): bool
  {
    return $this->update([
      'status' => self::STATUS_SENDING,
      'sent_at' => now()
    ]);
  }

  public function markAsSent(): bool
  {
    return $this->update([
      'status' => self::STATUS_SENT,
      'completed_at' => now(),
      'last_sent_at' => now()
    ]);
  }

  public function markAsFailed(string $error = null): bool
  {
    return $this->update([
      'status' => self::STATUS_FAILED,
      'completed_at' => now(),
      'metadata' => array_merge($this->metadata ?? [], [
        'last_error' => $error,
        'failed_at' => now()->toDateTimeString()
      ])
    ]);
  }

  // Tracking methods
  public function trackOpen(string $recipientUuid): void
  {
    if (!$this->track_opens) {
      return;
    }

    $recipient = $this->recipients()->where('uuid', $recipientUuid)->first();

    if ($recipient) {
      $this->opens()->create([
        'recipient_id' => $recipient->id,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent()
      ]);

      $this->recipients()->updateExistingPivot($recipient->id, [
        'opened_at' => now()
      ]);
    }
  }

  public function trackClick(string $recipientUuid, string $url): void
  {
    if (!$this->track_clicks) {
      return;
    }

    $recipient = $this->recipients()->where('uuid', $recipientUuid)->first();

    if ($recipient) {
      $this->clicks()->create([
        'recipient_id' => $recipient->id,
        'url' => $url,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent()
      ]);

      $this->recipients()->updateExistingPivot($recipient->id, [
        'clicked_at' => now()
      ]);
    }
  }

  public function trackUnsubscribe(string $recipientUuid, string $reason = null): void
  {
    $recipient = $this->recipients()->where('uuid', $recipientUuid)->first();

    if ($recipient) {
      $this->unsubscribes()->create([
        'recipient_id' => $recipient->id,
        'reason' => $reason,
        'ip_address' => request()->ip(),
        'user_agent' => request()->userAgent()
      ]);

      $recipient->update(['is_subscribed' => false]);
    }
  }

  // Add methods for detailed event analytics
  public function getDetailedStats(Carbon $startDate = null, Carbon $endDate = null)
  {
    $query = $this->emailLogs()
      ->with('events')
      ->when($startDate, fn($q) => $q->whereHas('events', fn($q) =>
      $q->where('timestamp', '>=', $startDate)
      ))
      ->when($endDate, fn($q) => $q->whereHas('events', fn($q) =>
      $q->where('timestamp', '<=', $endDate)
      ));

    return [
      'delivered' => $query->clone()->whereHas('events', fn($q) =>
      $q->where('event', 'delivered'))->count(),
      'opened' => $this->getOpenCount(),
      'clicked' => $this->getClickCount(),
      'bounced' => $query->clone()->whereHas('events', fn($q) =>
      $q->where('event', 'bounce'))->count(),
      'spam_reports' => $query->clone()->whereHas('events', fn($q) =>
      $q->where('event', 'spamreport'))->count(),
      'unsubscribed' => $this->getUnsubscribeCount(),
      'events' => $this->getEventTimeline($startDate, $endDate)
    ];
  }

  public function getEventTimeline(?Carbon $startDate = null, ?Carbon $endDate = null)
  {
    return $this->emailLogs()
      ->with(['events' => function ($query) use ($startDate, $endDate) {
        $query->when($startDate, fn($q) => $q->where('timestamp', '>=', $startDate))
          ->when($endDate, fn($q) => $q->where('timestamp', '<=', $endDate))
          ->orderBy('timestamp', 'desc');
      }])
      ->get()
      ->flatMap(fn($log) => $log->events)
      ->map(fn($event) => [
        'type' => $event->event,
        'timestamp' => $event->timestamp,
        'email' => $event->emailLog->email,
        'ip' => $event->ip,
        'user_agent' => $event->user_agent,
        'url' => $event->url,
        'reason' => $event->reason
      ])
      ->sortByDesc('timestamp')
      ->values();
  }
}
