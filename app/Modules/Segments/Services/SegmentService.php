<?php

namespace App\Modules\Segments\Services;

use App\Modules\Segments\Models\Segment;
use Illuminate\Support\Collection;

class SegmentService
{
  public function getMatchingRecipients(array $conditions, string $matchType = 'all'): Collection
  {
    $segment = new Segment([
      'conditions' => $conditions,
      'match_type' => $matchType
    ]);

    $query = \App\Models\Recipient::query();
    $segment->applyConditions($query);

    return $query->get();
  }

  public function previewSegment(array $conditions, string $matchType = 'all'): array
  {
    $recipients = $this->getMatchingRecipients($conditions, $matchType);

    return [
      'total_matches' => $recipients->count(),
      'sample_recipients' => $recipients->take(5),
      'demographics' => $this->calculateDemographics($recipients),
      'activity_stats' => $this->calculateActivityStats($recipients)
    ];
  }

  protected function calculateDemographics(Collection $recipients): array
  {
    return [
      'gender_distribution' => $recipients->groupBy('gender')
        ->map(fn($group) => $group->count()),
      'status_distribution' => $recipients->groupBy('status')
        ->map(fn($group) => $group->count())
    ];
  }

  protected function calculateActivityStats(Collection $recipients): array
  {
    return [
      'active_last_30_days' => $recipients->filter->isActive()->count(),
      'average_open_rate' => $recipients->avg(fn($recipient) =>
      $recipient->emailLogs()
        ->whereHas('events', fn($q) => $q->where('event', 'open'))
        ->count()
      )
    ];
  }
}
