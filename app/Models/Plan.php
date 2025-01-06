<?php

namespace App\Models;

use App\Traits\BootUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
  use HasFactory, BootUuid;

  protected $fillable = [
    'name',
    'uuid',
    'features'
  ];

  protected $casts = [
    'features' => 'array',
  ];

  public function formattedFeatures(): Attribute
  {
    $features = $this->features;

    return Attribute::make(
      get: fn() => [
        'campaign_limit' => "Up to {$features['campaign_limit']} campaigns",
        'recipient_limit' => "Up to {$features['recipient_limit']} recipients",
        'email_limit' => "Up to {$features['email_limit']} emails per month",
        'segment_limit' => "Up to {$features['segment_limit']} segments",
        'can_schedule_campaigns' => $features['can_schedule_campaigns'] ? 'Scheduled campaigns' : 'No campaign scheduling',
        'support_type' => $features['support_type'],
        'analytics' => $features['analytics'],
        'personalisation' => $features['personalisation'] ? 'Personalisation, including custom logo' : 'No personalisation',
      ],
    );
  }

}
