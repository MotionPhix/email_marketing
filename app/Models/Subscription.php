<?php

namespace App\Models;

use App\Traits\BootUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
  use HasFactory, BootUuid;

  protected $fillable = [
    'name',
    'campaign_limit',
    'recipient_limit',
    'email_limit',
    'segment_limit',
    'can_schedule_campaigns',
  ];
}
