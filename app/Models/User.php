<?php

namespace App\Models;

use App\Traits\HasSubscription;
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
  use HasApiTokens, BootUuid, HasRoles, HasSubscription;

  /** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory;
  use HasProfilePhoto;
  use Notifiable;
  use TwoFactorAuthenticatable;
  use SoftDeletes;

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
  ];

  /**
   * The accessors to append to the model's array form.
   *
   * @var array<int, string>
   */
  protected $appends = [
    'profile_photo_url', 'name'
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  public function name(): Attribute
  {
    return Attribute::get(fn() => $this->first_name . ' ' . $this->last_name);
  }

  public function campaigns()
  {
    return $this->hasMany(Campaign::class);
  }

  public function recipients()
  {
    return $this->hasMany(Recipient::class);
  }

  public function audiences()
  {
    return $this->hasMany(Audience::class);
  }

  public function templates()
  {
    return $this->hasMany(Template::class);
  }

  /**
   * Get the settings associated with the user.
   */
  public function settings()
  {
    return $this->hasOne(Setting::class);
  }

  public function subscription()
  {
    return $this->hasOne(Subscription::class)->latest();
  }

  public function hasPaidPlan(): bool
  {
    return $this->subscription()
      ->where('status', Subscription::STATUS_ACTIVE)
      ->whereNull('ends_at')
      ->orWhere('ends_at', '>', now())
      ->exists();
  }

  public function activeSubscription()
  {
    return $this->subscription()
      ->where('status', Subscription::STATUS_ACTIVE)
      ->where(function ($query) {
        $query->whereNull('ends_at')
          ->orWhere('ends_at', '>', now());
      })
      ->exists();
  }
}
