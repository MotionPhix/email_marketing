<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Segment extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'team_id',
    'name',
    'description',
    'conditions'
  ];

  protected $casts = [
    'conditions' => 'json'
  ];

  public function team(): BelongsTo
  {
    return $this->belongsTo(Team::class);
  }

  public function getSubscriberCountAttribute(): int
  {
    return $this->getSubscribersQuery()->count();
  }

  public function getSubscribersQuery()
  {
    $query = Subscriber::query()
      ->where('team_id', $this->team_id);

    foreach ($this->conditions as $condition) {
      $this->applyCondition($query, $condition);
    }

    return $query;
  }

  private function applyCondition($query, array $condition)
  {
    $field = $condition['field'];
    $operator = $condition['operator'];
    $value = $condition['value'];

    switch ($operator) {
      case 'equals':
        $query->where($field, $value);
        break;
      case 'contains':
        $query->where($field, 'like', "%{$value}%");
        break;
      case 'starts_with':
        $query->where($field, 'like', "{$value}%");
        break;
      case 'ends_with':
        $query->where($field, 'like', "%{$value}");
        break;
      // Add more operators as needed
    }
  }

  public function scopeForTeam($query, $team)
  {
    return $query->where('team_id', $team->id);
  }
}
