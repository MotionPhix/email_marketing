<?php

namespace App\Models;

use App\Traits\HasSubscription;
use App\Traits\HasEmailQuota;
use App\Traits\HasApiKeys;
use App\Traits\HasBranding;
use App\Traits\HasAnalytics;
use App\Traits\HasTeams;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\BootUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
  use HasApiTokens,
    BootUuid,
    HasRoles,
    HasSubscription,
    HasEmailQuota,
    HasApiKeys,
    HasBranding,
    HasAnalytics,
    HasTeams,
    HasFactory,
    HasProfilePhoto,
    Notifiable,
    TwoFactorAuthenticatable,
    SoftDeletes;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'first_name',
    'last_name',
    'email',
    'password',
    'company_name',
    'phone',
    'timezone',
    'language',
    'notification_preferences',
    'email_signature',
    'sendgrid_api_key',
    'default_sender_email',
    'default_sender_name',
    'account_status',
    'last_login_at',
    'last_login_ip',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
    'two_factor_recovery_codes',
    'two_factor_secret',
    'sendgrid_api_key',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
      'notification_preferences' => 'array',
      'last_login_at' => 'datetime',
      'trial_ends_at' => 'datetime',
      'email_quota' => 'integer',
      'account_status' => 'string',
    ];
  }

  /**
   * The accessors to append to the model's array form.
   *
   * @var array<int, string>
   */
  protected $appends = [
    'profile_photo_url',
    'name',
    'email_quota_remaining',
    'is_trial',
    'subscription_status'
  ];

  /**
   * Get the user's full name.
   */
  public function name(): Attribute
  {
    return Attribute::get(fn() => $this->first_name . ' ' . $this->last_name);
  }

  /**
   * Get user's email campaigns.
   */
  public function campaigns()
  {
    return $this->hasMany(Campaign::class)->latest();
  }

  /**
   * Get user's email templates.
   */
  public function templates()
  {
    return $this->hasMany(EmailTemplate::class);
  }

  /**
   * Get user's automation workflows.
   */
  public function automations()
  {
    return $this->hasMany(Automation::class);
  }

  /**
   * Get the settings associated with the user.
   */
  public function settings()
  {
    return $this->hasOne(Setting::class);
  }

  /**
   * Get user's API usage logs.
   */
  public function apiLogs()
  {
    return $this->hasMany(ApiLog::class);
  }

  /**
   * Get user's bounce logs.
   */
  public function bounceLogs()
  {
    return $this->hasMany(BounceLog::class);
  }

  /**
   * Check if user is within email quota limits.
   */
  public function hasEmailQuotaAvailable(): bool
  {
    return $this->email_quota_remaining > 0;
  }

  /**
   * Get remaining email quota.
   */
  public function emailQuotaRemaining(): Attribute
  {
    return Attribute::get(function() {
      $quotaUsed = $this->trackingEvents()
        ->where('type', 'sent')
        ->where('created_at', '>=', now()->startOfMonth())
        ->count();

      return max(0, $this->email_quota - $quotaUsed);
    });
  }

  /**
   * Check if user is in trial period.
   */
  public function isTrialStatus(): Attribute
  {
    return Attribute::get(
      fn() => $this->trial_ends_at && $this->trial_ends_at->isFuture()
    );
  }

  /**
   * Get current subscription status.
   */
  public function subscriptionStatus(): Attribute
  {
    return Attribute::get(function() {
      if ($this->is_trial) {
        return 'trial';
      }

      if ($this->hasActiveSubscription()) {
        return 'active';
      }

      return 'inactive';
    });
  }

  /**
   * Scope for active users.
   */
  public function scopeActive($query)
  {
    return $query->where('account_status', 'active');
  }

  /**
   * Record user login.
   */
  public function recordLogin(string $ip)
  {
    $this->update([
      'last_login_at' => now(),
      'last_login_ip' => $ip
    ]);
  }

  /**
   * Check if user can send emails.
   */
  public function canSendEmails(): bool
  {
    return $this->account_status === 'active' &&
      $this->hasEmailQuotaAvailable() &&
      ($this->hasActiveSubscription() || $this->is_trial);
  }

  /**
   * Get user's sending reputation score.
   */
  public function getReputationScore(): float
  {
    $totalSent = $this->trackingEvents()->where('type', 'sent')->count();
    if ($totalSent === 0) return 100.0;

    $bounces = $this->bounceLogs()->count();
    $complaints = $this->trackingEvents()->where('type', 'complaint')->count();

    $bounceRate = ($bounces / $totalSent) * 100;
    $complaintRate = ($complaints / $totalSent) * 100;

    return max(0, 100 - ($bounceRate * 2) - ($complaintRate * 5));
  }

  /**
   * Check if user needs sending warm-up.
   */
  public function needsWarmup(): bool
  {
    return $this->created_at->isAfter(now()->subDays(30)) &&
      $this->trackingEvents()->where('type', 'sent')->count() < 1000;
  }
}
