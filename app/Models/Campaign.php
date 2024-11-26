<?php

namespace App\Models;

use App\Traits\BootUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
  use HasFactory, BootUuid;

  protected $fillable = [
    'title', 'subject',
    'status', 'scheduled_at',
    'user_id', 'audience_id',
    'template_id', 'description',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function emailLogs()
  {
    return $this->hasMany(EmailLog::class);
  }

  public function audience()
  {
    return $this->belongsTo(Audience::class);
  }

  public function template()
  {
    return $this->belongsTo(Template::class);
  }

  // A campaign has many recipients through the audience
  public function recipients()
  {
    return $this->hasManyThrough(Recipient::class, Audience::class);
  }
}
