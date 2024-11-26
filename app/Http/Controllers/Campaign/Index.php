<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Index extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    $campaigns = DB::table('campaigns')
      ->select(
        'campaigns.id',
        'campaigns.uuid',
        'campaigns.title',
        'campaigns.template_id',
        'campaigns.scheduled_at',
        'campaigns.status',
        'campaigns.audience_id'
      , DB::raw('
        (SELECT COUNT(*)
         FROM recipients
         WHERE recipients.audience_id = campaigns.audience_id) as recipients_count
    '))
      ->where('campaigns.user_id', auth()->id())
      ->orderBy('campaigns.created_at', 'desc')
      ->paginate(10);

    return inertia('Campaigns/Index', [
      'campaigns' => $campaigns,
    ]);
  }
}
