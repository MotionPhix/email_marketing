<?php

namespace App\Modules\Segments\Models;

use App\Models\Recipient;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;

class Segment extends Model
{
  use SoftDeletes, HasUuids;

  protected $fillable = [
    'user_id',
    'name',
    'description',
    'conditions',
    'match_type', // 'all' or 'any'
    'last_applied_at',
    'metadata'
  ];

  protected $casts = [
    'conditions' => 'array',
    'metadata' => 'array',
    'last_applied_at' => 'datetime'
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function recipients()
  {
    return $this->belongsToMany(Recipient::class, 'segment_recipients')
      ->withTimestamps()
      ->withPivot(['added_at']);
  }

  public function applyConditions(Builder $query): Builder
  {
    foreach ($this->conditions as $condition) {
      $method = $this->match_type === 'all' ? 'where' : 'orWhere';

      $query->$method(function ($query) use ($condition) {
        switch ($condition['type']) {
          case 'field':
            $this->applyFieldCondition($query, $condition);
            break;
          case 'activity':
            $this->applyActivityCondition($query, $condition);
            break;
          case 'campaign':
            $this->applyCampaignCondition($query, $condition);
            break;
          case 'list':
            $this->applyListCondition($query, $condition);
            break;
        }
      });
    }

    return $query;
  }

  protected function applyFieldCondition($query, $condition): void
  {
    $operator = $this->mapOperator($condition['operator']);
    $query->where($condition['field'], $operator, $condition['value']);
  }

  protected function applyActivityCondition($query, $condition): void
  {
    switch ($condition['activity']) {
      case 'opened':
        $query->whereHas('emailLogs.events', function ($q) use ($condition) {
          $q->where('event', 'open')
            ->where('timestamp', '>=', now()->subDays($condition['days']));
        });
        break;
      case 'clicked':
        $query->whereHas('emailLogs.events', function ($q) use ($condition) {
          $q->where('event', 'click')
            ->where('timestamp', '>=', now()->subDays($condition['days']));
        });
        break;
      case 'not_opened':
        $query->whereDoesntHave('emailLogs.events', function ($q) use ($condition) {
          $q->where('event', 'open')
            ->where('timestamp', '>=', now()->subDays($condition['days']));
        });
        break;
    }
  }

  protected function applyCampaignCondition($query, $condition): void
  {
    switch ($condition['interaction']) {
      case 'received':
        $query->whereHas('campaigns', function ($q) use ($condition) {
          $q->where('campaigns.id', $condition['campaign_id']);
        });
        break;
      case 'not_received':
        $query->whereDoesntHave('campaigns', function ($q) use ($condition) {
          $q->where('campaigns.id', $condition['campaign_id']);
        });
        break;
    }
  }

  protected function applyListCondition($query, $condition): void
  {
    switch ($condition['membership']) {
      case 'in':
        $query->whereHas('lists', function ($q) use ($condition) {
          $q->where('lists.id', $condition['list_id']);
        });
        break;
      case 'not_in':
        $query->whereDoesntHave('lists', function ($q) use ($condition) {
          $q->where('lists.id', $condition['list_id']);
        });
        break;
    }
  }

  private function mapOperator(string $operator): string
  {
    return match ($operator) {
      'equals' => '=',
      'not_equals' => '!=',
      'contains' => 'LIKE',
      'not_contains' => 'NOT LIKE',
      'starts_with' => 'LIKE',
      'ends_with' => 'LIKE',
      default => $operator
    };
  }

  public function updateRecipients(): void
  {
    $query = Recipient::query();
    $this->applyConditions($query);

    // Get matching recipient IDs
    $recipientIds = $query->pluck('id');

    // Sync recipients
    $this->recipients()->sync($recipientIds->mapWithKeys(function ($id) {
      return [$id => ['added_at' => now()]];
    }));

    $this->update(['last_applied_at' => now()]);
  }
}
