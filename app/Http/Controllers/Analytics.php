<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class Analytics extends Controller
{
  public function __construct(
    private readonly AnalyticsService $analyticsService
  ) {}

  public function __invoke(Request $request)
  {
    $this->analyticsService->setUser(Auth::user());

    try {
      // Validate date range inputs if provided
      $validated = $request->validate([
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'period' => 'nullable|string|in:7d,30d,90d,1y,custom'
      ]);

      // Handle period selection
      $dates = $this->getDateRange($validated['period'] ?? '30d', [
        'start_date' => $validated['start_date'] ?? null,
        'end_date' => $validated['end_date'] ?? null,
      ]);

      // Get analytics data from service
      $dashboardData = $this->analyticsService->getDashboardStats(
        $dates['start']->toDateTimeString(),
        $dates['end']->toDateTimeString()
      );

      return Inertia::render('Dashboard', [
        ...$dashboardData,  // Spread operator since service returns exact structure needed
        'filters' => [
          'period' => $validated['period'] ?? '30d',
          'start_date' => $dates['start']->toDateString(),
          'end_date' => $dates['end']->toDateString(),
        ],
      ]);

    } catch (\Exception $e) {
      return Inertia::render('Dashboard', [
        'error' => config('app.debug')
          ? $e->getMessage()
          : 'An error occurred while fetching dashboard data.',
      ])->toResponse($request)
        ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

  private function getDateRange(string $period, array $customDates): array
  {
    $end = Carbon::now()->endOfDay();

    $start = match ($period) {
      '7d' => Carbon::now()->subDays(7)->startOfDay(),
      '30d' => Carbon::now()->subDays(30)->startOfDay(),
      '90d' => Carbon::now()->subDays(90)->startOfDay(),
      '1y' => Carbon::now()->subYear()->startOfDay(),
      'custom' => $customDates['start_date']
        ? Carbon::parse($customDates['start_date'])->startOfDay()
        : Carbon::now()->subDays(30)->startOfDay(),
      default => Carbon::now()->subDays(30)->startOfDay(),
    };

    if ($period === 'custom' && !empty($customDates['end_date'])) {
      $end = Carbon::parse($customDates['end_date'])->endOfDay();
    }

    return [
      'start' => $start,
      'end' => $end,
    ];
  }
}
