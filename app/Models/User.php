<?php

namespace App\Models;

use App\Traits\HasBootUuid;
use App\Traits\HasSubscription;
use App\Traits\HasEmailQuota;
use App\Traits\HasBranding;
use App\Traits\HasAnalytics;
use App\Traits\HasTeams;
use App\Traits\HasRegistrationSteps;
use Illuminate\Contracts\Auth\MustVerifyEmail;
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
    HasBootUuid,
    HasRoles,
    HasSubscription,
    HasEmailQuota,
    HasBranding,
    HasAnalytics,
    HasTeams,
    HasRegistrationSteps,
    HasFactory,
    HasProfilePhoto,
    Notifiable,
    TwoFactorAuthenticatable,
    SoftDeletes;

  /**
   * The attributes that are mass assignable.
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
    'industry',
    'company_size',
    'website',
    'role',
    'marketing_preferences',
    'registration_status',
    'completed_registration_steps',
    'registration_completed_at',
    'trial_ends_at',
    'email_quota'
  ];

  /**
   * The attributes that should be hidden for serialization.
   */
  protected $hidden = [
    'password',
    'remember_token',
    'two_factor_recovery_codes',
    'two_factor_secret',
    'sendgrid_api_key',
    'verification_token',
  ];

  /**
   * The attributes that should be cast.
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
      'notification_preferences' => 'array',
      'marketing_preferences' => 'array',
      'completed_registration_steps' => 'array',
      'last_login_at' => 'datetime',
      'trial_ends_at' => 'datetime',
      'registration_completed_at' => 'datetime',
      'email_quota' => 'integer',
      'account_status' => 'string',
    ];
  }

  /**
   * The accessors to append to the model's array form.
   */
  protected $appends = [
    'profile_photo_url',
    'name',
    'email_quota_remaining',
    'is_trial',
    'subscription_status',
    'registration_progress'
  ];

  const REGISTRATION_STEP_ACCOUNT = 1;
  const REGISTRATION_STEP_ORGANIZATION = 2;
  const REGISTRATION_STEP_TEAM = 3;
  const REGISTRATION_STEP_VERIFICATION = 4;

  // Keep existing relationships
  public function campaigns()
  {
    return $this->hasMany(Campaign::class)->latest();
  }

  public function templates()
  {
    return $this->hasMany(EmailTemplate::class);
  }

  public function invitedTeamMembers()
  {
    return $this->hasMany(InvitedTeamMember::class);
  }

  public function onboardingProgress()
  {
    return $this->hasOne(OnboardingProgress::class);
  }

  public function teamInvitations()
  {
    return $this->hasMany(InvitedTeamMember::class, 'email', 'email');
  }

  public function sentTeamInvitations()
  {
    return $this->hasMany(InvitedTeamMember::class, 'user_id');
  }

  public function campaignStats()
  {
    return $this->hasManyThrough(CampaignStats::class, Campaign::class);
  }

  public function campaignEvents()
  {
    return $this->hasManyThrough(CampaignEvent::class, Campaign::class);
  }

  public function subscribers()
  {
    return $this->hasMany(Subscriber::class);
  }

  // Keep existing computed attributes
  public function name(): Attribute
  {
    return Attribute::get(fn() => $this->first_name . ' ' . $this->last_name);
  }

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

  public function isTrialStatus(): Attribute
  {
    return Attribute::get(
      fn() => $this->trial_ends_at && $this->trial_ends_at->isFuture()
    );
  }

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

  // Add new registration-related computed attribute
  public function registrationProgress(): Attribute
  {
    return Attribute::get(function() {
      $totalSteps = count(self::getRegistrationSteps());
      $completedSteps = count($this->completed_registration_steps ?? []);
      return [
        'current_step' => $this->getCurrentRegistrationStep(),
        'completed_steps' => $completedSteps,
        'total_steps' => $totalSteps,
        'percentage' => $totalSteps > 0 ? ($completedSteps / $totalSteps) * 100 : 0,
      ];
    });
  }

  // Keep existing methods
  public function scopeActive($query)
  {
    return $query->where('account_status', 'active');
  }

  public function recordLogin(string $ip)
  {
    $this->update([
      'last_login_at' => now(),
      'last_login_ip' => $ip
    ]);
  }

  public function hasEmailQuotaAvailable(): bool
  {
    return $this->email_quota_remaining > 0;
  }

  public function canSendEmails(): bool
  {
    return $this->account_status === 'active' &&
      $this->hasEmailQuotaAvailable() &&
      ($this->hasActiveSubscription() || $this->is_trial);
  }

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

  public function needsWarmup(): bool
  {
    return $this->created_at->isAfter(now()->subDays(30)) &&
      $this->trackingEvents()->where('type', 'sent')->count() < 1000;
  }

  // Add registration-specific methods
  public function hasCompletedStep(int $step): bool
  {
    return in_array($step, $this->completed_registration_steps ?? []);
  }

  public function getCurrentRegistrationStep(): int
  {
    $completedSteps = $this->completed_registration_steps ?? [];
    return empty($completedSteps) ? 1 : max($completedSteps) + 1;
  }

  public function completeRegistrationStep(int $step, array $data): void
  {
    $this->registrationData()->create([
      'step' => $step,
      'data' => $data,
      'completed_at' => now(),
    ]);

    $completedSteps = $this->completed_registration_steps ?? [];
    $completedSteps[] = $step;
    $this->completed_registration_steps = array_unique($completedSteps);

    if ($step === count(self::getRegistrationSteps())) {
      $this->registration_status = 'completed';
      $this->registration_completed_at = now();

      // Start trial period
      $this->trial_ends_at = now()->addDays(14);
    }

    $this->save();
  }

  public function hasCompletedRegistration(): bool
  {
    return $this->registration_status === 'completed';
  }
}
