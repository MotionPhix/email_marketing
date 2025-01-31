<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Recipient;
use App\Models\EmailEvent;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use ProtoneMedia\LaravelCrossEloquentSearch\Search;
use Carbon\Carbon;

class Index extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    $query = Recipient::where('user_id', $request->user()->id)
      ->withoutTrashed()
      ->withLastActivity()
      ->with(['audiences', 'unsubscribes']);

    $this->applySearch($query, $request)
      ->applyFilters($query, $request)
      ->applySorting($query, $request);

    return inertia('Recipients/Index', [
      'recipients' => $query->paginate($request->input('per_page', 10))->withQueryString(),
      'filters' => $this->getActiveFilters($request),
      'stats' => $this->getRecipientStats($request->user()->id)
    ]);
  }

  /**
   * Apply search conditions to the query
   */
  private function applySearch(Builder $query, Request $request): self
  {
    if ($search = $request->input('search')) {
      $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%");
      });
    }

    return $this;
  }

  /**
   * Apply all filters to the query
   */
  private function applyFilters(Builder $query, Request $request): self
  {
    // Basic filters
    if ($status = $request->input('status')) {
      $query->where('status', $status);
    }

    if ($gender = $request->input('gender')) {
      $query->where('gender', $gender);
    }

    // Date range filter
    if ($dateRange = $request->input('date_range')) {
      $dates = explode(',', $dateRange);
      if (count($dates) === 2) {
        $query->whereBetween('created_at', [
          Carbon::parse($dates[0])->startOfDay(),
          Carbon::parse($dates[1])->endOfDay()
        ]);
      }
    }

    // Activity filter using email events
    if ($activity = $request->input('activity')) {
      $thirtyDaysAgo = Carbon::now()->subDays(30);

      switch ($activity) {
        case 'active':
          $query->active();
          break;
        case 'inactive':
          $query->inactive();
          break;
        case 'never':
          $query->whereDoesntHave('emailLogs');
          break;
      }
    }

    // Audience filter
    if ($audienceId = $request->input('audience_id')) {
      $query->whereHas('audiences', function ($q) use ($audienceId) {
        $q->where('audiences.id', $audienceId);
      });
    }

    // Unsubscribe filter
    if ($request->boolean('unsubscribed')) {
      $query->whereHas('unsubscribes');
    }

    return $this;
  }

  /**
   * Apply sorting to the query
   */
  private function applySorting(Builder $query, Request $request): self
  {
    $sortField = $request->input('sort_by', 'name');
    $sortDirection = $request->input('sort_direction', 'asc');

    $allowedSortFields = [
      'name',
      'email',
      'status',
      'created_at',
      'last_activity'
    ];

    if (in_array($sortField, $allowedSortFields)) {
      if ($sortField === 'last_activity') {
        $query->orderBy(
          EmailEvent::select('timestamp')
            ->whereColumn('email_logs.email', 'recipients.email')
            ->join('email_logs', 'email_events.email_log_id', '=', 'email_logs.id')
            ->latest('timestamp')
            ->limit(1),
          $sortDirection
        );
      } else {
        $query->orderBy($sortField, $sortDirection);
      }
    }

    return $this;
  }

  /**
   * Get active filters for the frontend
   */
  private function getActiveFilters(Request $request): array
  {
    return array_filter([
      'status' => $request->input('status'),
      'gender' => $request->input('gender'),
      'date_range' => $request->input('date_range'),
      'activity' => $request->input('activity'),
      'audience_id' => $request->input('audience_id'),
      'unsubscribed' => $request->boolean('unsubscribed'),
      'search' => $request->input('search')
    ]);
  }

  /**
   * Get recipient statistics
   */
  private function getRecipientStats(int $userId): array
  {
    $baseQuery = Recipient::where('user_id', $userId);
    $thirtyDaysAgo = Carbon::now()->subDays(30);

    return [
      'total' => $baseQuery->count(),
      'active' => (clone $baseQuery)->active()->count(),
      'unsubscribed' => (clone $baseQuery)->whereHas('unsubscribes')->count(),
      'recent' => (clone $baseQuery)->where('created_at', '>=', $thirtyDaysAgo)->count()
    ];
  }
}
