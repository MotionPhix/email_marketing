<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
  protected $fillable = [
    'user_id',
    'preferences',
    'notification_settings',
    'email_settings',
    'sender_settings',
    'marketing_settings',
    'branding_settings',
    'company_settings',
    'subscription_settings',
  ];

  protected $casts = [
    'preferences' => 'array',
    'notification_settings' => 'array',
    'email_settings' => 'array',
    'sender_settings' => 'array',
    'marketing_settings' => 'array',
    'branding_settings' => 'array',
    'company_settings' => 'array',
    'subscription_settings' => 'array',
  ];

  protected $attributes = [
    'subscription_settings' => '{
      "plan": "free",
      "email_quota": 100,
      "features": {
        "custom_domain": false,
        "api_access": false,
        "advanced_analytics": false
      }
    }',
    'company_settings' => '{
      "company_name": null,
      "industry": null,
      "company_size": null,
      "website": null,
      "phone": null,
      "role": null
    }',
    'sender_settings' => '{
      "default_sender_name": null,
      "default_sender_email": null,
      "email_verified": false,
      "verification_token": null
    }',
    'marketing_settings' => '{
      "email_updates": true,
      "product_news": true,
      "marketing_communications": true
    }'
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  // Convenient accessors
  public function getEmailVerifiedAttribute(): bool
  {
    return $this->sender_settings['email_verified'] ?? false;
  }

  public function getCompanyNameAttribute(): ?string
  {
    return $this->company_settings['company_name'] ?? null;
  }

  public function getDefaultSenderNameAttribute(): ?string
  {
    return $this->sender_settings['default_sender_name'] ?? null;
  }

  public function getDefaultSenderEmailAttribute(): ?string
  {
    return $this->sender_settings['default_sender_email'] ?? null;
  }

  public function getEmailQuotaAttribute(): int
  {
    return $this->subscription_settings['email_quota'] ?? 100;
  }
}
