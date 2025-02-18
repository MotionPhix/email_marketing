<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
  protected $fillable = [
    'name',
    'owner_id',
    'personal_team',
    'settings'
  ];

  protected $casts = [
    'personal_team' => 'boolean',
    'settings' => 'array'
  ];

  public function owner(): BelongsTo
  {
    return $this->belongsTo(User::class, 'owner_id');
  }

  public function users(): BelongsToMany
  {
    return $this->belongsToMany(User::class)
      ->withPivot('role')
      ->withTimestamps();
  }

  public function campaigns()
  {
    return $this->hasMany(Campaign::class);
  }

  public function templates()
  {
    return $this->hasMany(EmailTemplate::class);
  }

  public function subscribers()
  {
    return $this->hasMany(Subscriber::class);
  }

  public function invitations(): HasMany
  {
    return $this->hasMany(InvitedTeamMember::class);
  }
}
