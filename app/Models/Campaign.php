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

  protected $fillable = [
    'title',
    'subject',
    'status',
    'scheduled_at',
    'user_id',
    'audience_id',
    'template_id',
    'description',
    'frequency',
    'send_at',
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
      get: fn () => $this->scheduled_at ? Carbon::parse($this->scheduled_at)->format('D, d M, Y') : null,
    );
  }

  public function formattedEndDate(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->end_date ? Carbon::parse($this->end_date)->format('D, d M, Y') : null,
    );
  }
}
