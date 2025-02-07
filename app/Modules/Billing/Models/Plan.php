<?php

namespace App\Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class Plan extends Model
{
  use HasUuids;

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

  protected static function boot(): void
  {
    parent::boot();

    static::creating(function ($plan) {
      if (empty($plan->slug)) {
        $plan->slug = Str::slug($plan->name);
      }
    });
  }

  public function subscriptions(): HasMany
  {
    return $this->hasMany(Subscription::class);
  }

  public function scopeActive(Builder $query): Builder
  {
    return $query->where('is_active', true);
  }

  public function scopeOrdered(Builder $query): Builder
  {
    return $query->orderBy('sort_order')->orderBy('price');
  }

  public function getCampaignLimit(): int
  {
    return (int) ($this->features['campaign_limit'] ?? 0);
  }

  public function getEmailLimit(): int
  {
    return (int) preg_replace('/[^0-9]/', '', $this->features['email_limit'] ?? '0');
  }

  public function getRecipientLimit(): int
  {
    return (int) preg_replace('/[^0-9]/', '', $this->features['recipient_limit'] ?? '0');
  }

  public function canScheduleCampaigns(): bool
  {
    return !empty($this->features['can_schedule_campaigns']);
  }

  public function hasPersonalisation(): bool
  {
    return !empty($this->features['personalisation']);
  }

  public function getFormattedPrice(): string
  {
    return number_format($this->price / 100, 2) . ' ' . $this->currency;
  }
}
