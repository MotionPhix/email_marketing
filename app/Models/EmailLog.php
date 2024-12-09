<?php

namespace App\Models;

use App\Traits\BootUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
  use HasFactory, BootUuid;

  protected $fillable = [
    'campaign_uuid',
    'sg_message_id',
    'email',
    'user_uuid'
  ];

  public function campaign()
  {
    return $this->belongsTo(Campaign::class);
  }

  public function events()
  {
    return $this->hasMany(EmailEvent::class);
  }

}
