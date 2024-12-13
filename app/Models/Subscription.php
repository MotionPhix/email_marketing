<?php

namespace App\Models;

use App\Traits\BootUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
  use HasFactory, BootUuid;

//{
//"campaign_limit": 50,
//"recipient_limit": 500,
//"email_limit": 1000,
//"segment_limit": 10,
//"can_schedule_campaigns": true,
//"support_type": "Priority",
//"analytics": true,
//"dedicated_account_manager": false
//}

  protected $fillable = [
    'name',
    'uuid',
    'features'
  ];

  protected $casts = [
    'features' => 'array',
  ];
}
