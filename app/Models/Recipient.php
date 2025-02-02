<?php

namespace App\Models;

use App\Traits\BootUuid;
use Carbon\Carbon;
use Database\Factories\RecipientFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipient extends Model
{
  use HasFactory, BootUuid, SoftDeletes;

  // Define status constants
  public const STATUS_ACTIVE = 'active';
  public const STATUS_INACTIVE = 'inactive';
  public const STATUS_UNSUBSCRIBED = 'unsubscribed';
  public const STATUS_BOUNCED = 'bounced';
  public const STATUS_NEW = 'new';

  // Define activity thresholds
  public const INACTIVE_THRESHOLD_DAYS = 30;
  public const NEW_RECIPIENT_THRESHOLD_DAYS = 7;

  protected $fillable = [
    'audience_id',
    'email',
    'name',
    'user_id',
    'gender'
  ];

  protected $appends = ['current_status'];

  // Relationships
  public function audiences()
  {
    return $this->belongsToMany(
      Audience::class,
      'audience_recipient',
      'recipient_id',
      'audience_id'
    );
  }

  public function campaigns()
  {
    return $this->belongsToMany(Campaign::class, 'campaign_recipients');
  }

  public function emailLogs()
  {
    return $this->hasMany(EmailLog::class, 'email', 'email');
  }

  public function unsubscribes()
  {
    return $this->hasMany(CampaignUnsubscribe::class);
  }

  public function lastEmailEvent()
  {
    return $this->hasManyThrough(
      EmailEvent::class,
      EmailLog::class,
      'email',
      'email_log_id',
      'email',
      'id'
    )->latest('timestamp');
  }

  // Dynamic status calculation
  public function getCurrentStatusAttribute()
  {
    if ($this->isUnsubscribed()) {
      return self::STATUS_UNSUBSCRIBED;
    }

    if ($this->hasBounced()) {
      return self::STATUS_BOUNCED;
    }

    if ($this->isNew()) {
      return self::STATUS_NEW;
    }

    return $this->isActive() ? self::STATUS_ACTIVE : self::STATUS_INACTIVE;
  }

  // Status check methods
  public function isActive(): bool
  {
    return $this->emailLogs()
      ->whereHas('events', function ($query) {
        $query->where('event', 'opened')
          ->where('timestamp', '>=', now()->subDays(self::INACTIVE_THRESHOLD_DAYS));
      })
      ->exists();
  }

  public function isNew(): bool
  {
    return Carbon::parse($this->created_at)->diffInDays(now()) <= self::NEW_RECIPIENT_THRESHOLD_DAYS
      && !$this->emailLogs()->exists();
  }

  public function isUnsubscribed(): bool
  {
    return $this->unsubscribes()->exists();
  }

  public function hasBounced(): bool
  {
    return $this->emailLogs()
      ->whereHas('events', function ($query) {
        $query->where('event', 'bounce')
          ->orWhere('event', 'dropped');
      })
      ->exists();
  }

  // Scopes
  public function scopeWithLastActivity(Builder $query)
  {
    return $query->addSelect([
      'last_activity' => EmailEvent::select('timestamp')
        ->whereColumn('email_logs.email', 'recipients.email')
        ->join('email_logs', 'email_events.email_log_id', '=', 'email_logs.id')
        ->latest('timestamp')
        ->limit(1)
    ]);
  }

  public function scopeActive(Builder $query)
  {
    return $query->whereHas('emailLogs.events', function ($query) {
      $query->where('event', 'opened')
        ->where('timestamp', '>=', now()->subDays(self::INACTIVE_THRESHOLD_DAYS));
    });
  }

  public function scopeInactive(Builder $query)
  {
    $activeIds = (clone $query)->active()->pluck('id');
    return $query->whereNotIn('id', $activeIds)
      ->whereDoesntHave('unsubscribes')
      ->whereHas('emailLogs');
  }

  public function scopeNew(Builder $query)
  {
    return $query->where('created_at', '>=', now()->subDays(self::NEW_RECIPIENT_THRESHOLD_DAYS))
      ->whereDoesntHave('emailLogs');
  }

  public function scopeUnsubscribed(Builder $query)
  {
    return $query->whereHas('unsubscribes');
  }

  public function scopeBounced(Builder $query)
  {
    return $query->whereHas('emailLogs.events', function ($query) {
      $query->where('event', 'bounce')
        ->orWhere('event', 'dropped');
    });
  }

  protected static function newFactory()
  {
    return RecipientFactory::new();
  }
}
