<?php

namespace App\Models;

use App\Traits\BootUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
  use HasFactory, BootUuid;

  // Add campaign status constants
  public const STATUS_DRAFT = 'draft';
  public const STATUS_SCHEDULED = 'scheduled';
  public const STATUS_SENDING = 'sending';
  public const STATUS_SENT = 'sent';
  public const STATUS_FAILED = 'failed';

  // Add step constants
  public const STEP_DETAILS = 1;
  public const STEP_TEMPLATE = 2;
  public const STEP_AUDIENCE = 3;

  protected $fillable = [
    'title',
    'subject',
    'status',
    'step',
    'scheduled_at',
    'user_id',
    'audience_id',
    'template_id',
    'description',
    'frequency',
    'send_at',
  ];

  protected $casts = [
    'scheduled_at' => 'datetime',
    'send_at' => 'datetime',
    'end_date' => 'datetime'
  ];

  protected $attributes = [
    'step' => self::STEP_DETAILS, // Default to first step
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function emailLogs()
  {
    return $this->hasMany(EmailLog::class, 'campaign_uuid');
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

  public function unsubscribes()
  {
    return $this->hasMany(CampaignUnsubscribe::class);
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

  // Add scope for active campaigns
  public function scopeActive($query) {
    return $query->whereIn('status', [
      self::STATUS_SCHEDULED,
      self::STATUS_SENDING
    ]);
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
}
