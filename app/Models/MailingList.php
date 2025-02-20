<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MailingList extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'team_id',
    'name',
    'description',
    'settings',
    'is_default',
    'status'
  ];

  protected $casts = [
    'settings' => 'json',
    'is_default' => 'boolean'
  ];

  public function team(): BelongsTo
  {
    return $this->belongsTo(Team::class);
  }

  public function subscribers(): BelongsToMany
  {
    return $this->belongsToMany(Subscriber::class, 'subscriber_mailing_list')
      ->withTimestamps()
      ->withPivot('status');
  }

  public function getSubscriberCountAttribute(): int
  {
    return $this->subscribers()
      ->wherePivot('status', 'subscribed')
      ->count();
  }

  public function scopeForTeam($query, $team)
  {
    return $query->where('team_id', $team->id);
  }
}
