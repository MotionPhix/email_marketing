<?php

namespace App\Models;

use App\Traits\BootUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Plan extends Model
{
  use HasFactory, BootUuid;

  protected $fillable = [
    'name',
    'slug',
    'description',
    'price',
    'currency',
    'trial_days',
    'is_active',
    'is_featured',
    'sort_order',
    'features',
    'metadata'
  ];

  protected $casts = [
    'features' => 'array',
    'metadata' => 'array',
    'price' => 'integer',
    'is_active' => 'boolean',
    'is_featured' => 'boolean',
    'trial_days' => 'integer',
    'sort_order' => 'integer',
  ];

  protected static function boot()
  {
    parent::boot();

    static::creating(function ($plan) {
      if (empty($plan->slug)) {
        $plan->slug = Str::slug($plan->name);
      }
    });
  }

  // Scopes
  public function scopeActive(Builder $query): Builder
  {
    return $query->where('is_active', true);
  }

  public function scopeFeatured(Builder $query): Builder
  {
    return $query->where('is_featured', true);
  }

  public function scopeOrdered(Builder $query): Builder
  {
    return $query->orderBy('sort_order')->orderBy('price');
  }

  // Feature helper methods
  public function getCampaignLimit(): int
  {
    return (int) $this->features['campaign_limit'] ?? 0;
  }

  public function getEmailLimit(): int
  {
    return (int) preg_replace('/[^0-9]/', '', $this->features['email_limit'] ?? '0');
  }

  public function getRecipientLimit(): int
  {
    return (int) preg_replace('/[^0-9]/', '', $this->features['recipient_limit'] ?? '0');
  }

  public function getSegmentLimit(): int
  {
    return (int) preg_replace('/[^0-9]/', '', $this->features['segment_limit'] ?? '0');
  }

  public function canScheduleCampaigns(): bool
  {
    return !empty($this->features['can_schedule_campaigns']);
  }

  public function hasPersonalisation(): bool
  {
    return !empty($this->features['personalisation']);
  }

  public function getAnalyticsLevel(): string
  {
    return $this->features['analytics'] ?? 'No analytics';
  }

  public function getSupportType(): string
  {
    return $this->features['support_type'] ?? 'No support';
  }

  // Formatted features for display
  public function getFormattedFeatures(): array
  {
    return [
      'campaign_limit' => "Up to {$this->getCampaignLimit()} campaigns",
      'recipient_limit' => "Up to {$this->getRecipientLimit()} recipients",
      'email_limit' => $this->features['email_limit'] ?? 'No emails',
      'segment_limit' => $this->features['segment_limit'] ?? 'No segments',
      'can_schedule_campaigns' => $this->canScheduleCampaigns() ? 'Scheduled campaigns' : 'No campaign scheduling',
      'support_type' => $this->getSupportType(),
      'analytics' => $this->getAnalyticsLevel(),
      'personalisation' => $this->hasPersonalisation() ? 'Full personalisation' : 'Basic personalisation',
    ];
  }

  // Price formatting
  public function getFormattedPrice(): string
  {
    return number_format($this->price, 2) . ' ' . $this->currency;
  }

  // Relationships
  public function subscriptions()
  {
    return $this->hasMany(Subscription::class);
  }

  public function activeSubscriptions()
  {
    return $this->subscriptions()
      ->where('status', Subscription::STATUS_ACTIVE)
      ->where(function ($query) {
        $query->whereNull('ends_at')
          ->orWhere('ends_at', '>', now());
      });
  }

  // Compare features with another plan
  public function compareWith(Plan $plan): array
  {
    $differences = [];
    $thisFeatures = $this->getFormattedFeatures();
    $otherFeatures = $plan->getFormattedFeatures();

    foreach ($thisFeatures as $key => $value) {
      if ($value !== $otherFeatures[$key]) {
        $differences[$key] = [
          'current' => $value,
          'other' => $otherFeatures[$key]
        ];
      }
    }

    return $differences;
  }

  // Check if this plan is an upgrade from another plan
  public function isUpgradeFrom(Plan $plan): bool
  {
    return $this->price > $plan->price;
  }
}
