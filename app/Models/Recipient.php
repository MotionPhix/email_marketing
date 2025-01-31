<?php

namespace App\Models;

use App\Traits\BootUuid;
use Database\Factories\RecipientFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipient extends Model
{
  use HasFactory, BootUuid, SoftDeletes;

  protected $fillable = ['audience_id', 'status', 'email', 'name', 'user_id', 'gender'];

  public function audiences()
  {
    return $this->belongsToMany(
      Audience::class,
      'audience_recipient',
      'recipient_id',
      'audience_id'
    );
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function unsubscribes()
  {
    return $this->hasMany(CampaignUnsubscribe::class);
  }

  public function emailLogs()
  {
    return $this->hasMany(EmailLog::class, 'email', 'email');
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

  public function scopeInactive(Builder $query, $days = 30)
  {
    return $query->whereDoesntHave('emailLogs.events', function ($query) use ($days) {
      $query->where('timestamp', '>=', now()->subDays($days));
    });
  }

  public function scopeActive(Builder $query, $days = 30)
  {
    return $query->whereHas('emailLogs.events', function ($query) use ($days) {
      $query->where('timestamp', '>=', now()->subDays($days));
    });
  }

  public function unsubscribedFromCampaign(Campaign $campaign)
  {
    return $this->unsubscribes()
      ->where('campaign_id', $campaign->id)
      ->exists();
  }

  protected static function newFactory()
  {
    return RecipientFactory::new();
  }
}
