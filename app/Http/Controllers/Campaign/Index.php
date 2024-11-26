<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;

class Index extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    $campaigns = Campaign::where('user_id', auth()->id())
      ->latest()->paginate(10);

    return inertia('Campaigns/Index', [
      'campaigns' => $campaigns,
    ]);
  }
}
