<?php

namespace App\Traits;

use App\Models\Team;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

trait HasTeams
{
  public function teams(): BelongsToMany
  {
    return $this->belongsToMany(Team::class)
      ->withPivot('role')
      ->withTimestamps();
  }

  public function ownedTeams(): HasMany
  {
    return $this->hasMany(Team::class, 'owner_id');
  }

  public function currentTeam()
  {
    return $this->belongsTo(Team::class, 'current_team_id');
  }

  public function switchTeam($team): bool
  {
    if (!$this->belongsToTeam($team)) {
      return false;
    }

    $this->forceFill([
      'current_team_id' => $team->id,
    ])->save();

    $this->setRelation('currentTeam', $team);

    return true;
  }

  public function allTeams(): Collection
  {
    return $this->ownedTeams->merge($this->teams);
  }

  public function teamRole($team): ?string
  {
    if ($this->id === $team->owner_id) {
      return 'owner';
    }

    if ($this->teams->contains($team)) {
      return $this->teams->find($team->id)->pivot->role;
    }

    return null;
  }

  public function hasTeamRole($team, string $role): bool
  {
    return $this->teamRole($team) === $role;
  }

  public function isOwnerOf($team): bool
  {
    return $this->id === $team->owner_id;
  }

  public function belongsToTeam($team): bool
  {
    return $this->teams->contains($team) || $this->ownsTeam($team);
  }

  public function ownsTeam($team): bool
  {
    return $this->ownedTeams->contains($team);
  }
}
