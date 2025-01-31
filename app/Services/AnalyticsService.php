<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmailEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
  private const CACHE_TTL = 300;
  private User $user;

  public function setUser(User $user): void
  {
    $this->user = $user;
  }

  public function getDashboardStats(string $startDate = null, string $endDate = null): array
  {
    $startDate = $startDate ?? Carbon::now()->subDays(30)->startOfDay();
    $endDate = $endDate ?? Carbon::now()->endOfDay();
    $cacheKey = "dashboard_stats_{$this->user->id}_{$startDate}_{$endDate}";

    return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($startDate, $endDate) {
      // Get current period stats
      $currentStats = $this->getPeriodStats($startDate, $endDate);

      // Get previous period for comparison
      $previousStart = Carbon::parse($startDate)->subDays(
        Carbon::parse($endDate)->diffInDays(Carbon::parse($startDate))
      );
      $previousEnd = Carbon::parse($startDate)->subDay();
      $previousStats = $this->getPeriodStats($previousStart, $previousEnd);

      return [
        'stats' => [
          'total_recipients' => $this->user->recipients()->count(),
          'total_campaigns' => $this->user->campaigns()->count(),
          'total_sent' => $currentStats['processed'] ?? 0,
          'delivered' => [
            'count' => $currentStats['delivered'] ?? 0,
            'percentage' => $this->calculateRate(
              $currentStats['delivered'] ?? 0,
              $currentStats['processed'] ?? 0
            )
          ],
          'opened' => [
            'count' => $currentStats['open'] ?? 0,
            'percentage' => $this->calculateRate(
              $currentStats['open'] ?? 0,
              $currentStats['delivered'] ?? 0
            )
          ],
          'clicked' => [
            'count' => $currentStats['click'] ?? 0,
            'percentage' => $this->calculateRate(
              $currentStats['click'] ?? 0,
              $currentStats['delivered'] ?? 0
            )
          ],
          'bounced' => [
            'count' => $currentStats['bounce'] ?? 0,
            'percentage' => $this->calculateRate(
              $currentStats['bounce'] ?? 0,
              $currentStats['processed'] ?? 0
            )
          ],
          'spam' => [
            'count' => $currentStats['spamreport'] ?? 0,
            'percentage' => $this->calculateRate(
              $currentStats['spamreport'] ?? 0,
              $currentStats['delivered'] ?? 0
            )
          ],
          'unsubscribed' => [
            'count' => $currentStats['unsubscribe'] ?? 0,
            'percentage' => $this->calculateRate(
              $currentStats['unsubscribe'] ?? 0,
              $currentStats['delivered'] ?? 0
            )
          ]
        ],
        'chartData' => $this->getChartData($startDate, $endDate),
        'recentEvents' => $this->getRecentEvents($startDate, $endDate),
        'periodComparison' => [
          'delivered' => $previousStats['delivered'] ?? 0,
          'opened' => $previousStats['open'] ?? 0,
          'clicked' => $previousStats['click'] ?? 0,
          'bounced' => $previousStats['bounce'] ?? 0
        ]
      ];
    });
  }

  private function getPeriodStats(string $startDate, string $endDate): array
  {
    return EmailEvent::whereHas('emailLog.campaign', function ($query) {
      $query->where('user_id', $this->user->id);
    })
      ->whereBetween('timestamp', [$startDate, $endDate])
      ->select('event', DB::raw('COUNT(*) as count'))
      ->groupBy('event')
      ->get()
      ->pluck('count', 'event')
      ->toArray();
  }

  private function getChartData(string $startDate, string $endDate): array
  {
    return EmailEvent::whereHas('emailLog.campaign', function ($query) {
      $query->where('user_id', $this->user->id);
    })
      ->whereBetween('timestamp', [$startDate, $endDate])
      ->select(
        'event',
        DB::raw('DATE(timestamp) as date'),
        DB::raw('COUNT(*) as count')
      )
      ->whereIn('event', ['delivered', 'open', 'click', 'bounce', 'spamreport'])
      ->groupBy('event', 'date')
      ->get()
      ->groupBy('event')
      ->map(function ($group) {
        return $group->pluck('count', 'date')->toArray();
      })
      ->toArray();
  }

  private function getRecentEvents(string $startDate, string $endDate, int $limit = 20): array
  {
    return EmailEvent::with('emailLog:id,email')
      ->whereHas('emailLog.campaign', function ($query) {
        $query->where('user_id', $this->user->id);
      })
      ->whereBetween('timestamp', [$startDate, $endDate])
      ->whereIn('event', ['delivered', 'open', 'click', 'bounce', 'spamreport', 'unsubscribe'])
      ->latest('timestamp')
      ->take($limit)
      ->get(['event', 'email_log_id', 'timestamp'])
      ->toArray();
  }

  private function calculateRate(int $numerator, int $denominator): float
  {
    if ($denominator === 0) return 0;
    return round(($numerator / $denominator) * 100, 1);
  }
}
