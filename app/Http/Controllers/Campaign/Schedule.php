<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;

class Schedule extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Campaign $campaign)
  {
    if (!$request->user()->canScheduleCampaigns() && $request->has('scheduled_at')) {
      return back()->with('error', 'Campaign scheduling is not available in your current plan.');
    }

    return Inertia('Campaigns/Components/Schedule', [
      'campaign' => $campaign
    ]);
  }
}
