<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Jobs\ScheduleCampaignJob;
use App\Models\Campaign;
use App\Services\Campaign\CampaignSchedulingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class Console extends Controller
{
  public function __construct(
    private CampaignSchedulingService $schedulingService
  ) {}

  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Campaign $campaign)
  {
    $validated = $request->validate([
      'scheduled_at' => 'required|date|after:now',
      'frequency' => ['required', Rule::in(array_keys($this->schedulingService::FREQUENCIES))],
      'end_date' => [
        'nullable',
        'date',
        'after:scheduled_at',
        Rule::requiredIf(fn() => $request->frequency !== 'once')
      ],
    ]);

    try {
      $scheduledAt = Carbon::parse($validated['scheduled_at']);
      $endDate = isset($validated['end_date']) ? Carbon::parse($validated['end_date']) : null;

      $this->schedulingService->schedule(
        $campaign,
        $scheduledAt,
        $validated['frequency'],
        $endDate
      );

      return response()->json([
        'message' => 'Campaign scheduled successfully'
      ]);

    } catch (\Exception $e) {
      Log::error('Campaign scheduling failed', [
        'campaign' => $campaign->uuid,
        'error' => $e->getMessage()
      ]);

      return response()->json([
        'message' => 'Failed to schedule campaign'
      ], 422);
    }
  }

  /**
   * Redirect with a flash message.
   */
  private function redirectWithFlash(string $style, string $message)
  {
    return redirect()->back()->with('flash', [
      'bannerStyle' => $style,
      'banner' => $message,
    ]);
  }
}
