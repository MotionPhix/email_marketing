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
        'campaigns.audience_id',
        'audiences.name as audience_name',
        DB::raw('
          (SELECT COUNT(*)
           FROM audience_recipient
           WHERE audience_recipient.audience_id = campaigns.audience_id
          ) as recipients_count
        ')
      )
      ->join('audiences', 'campaigns.audience_id', '=', 'audiences.id')
      ->where('campaigns.user_id', auth()->id())
      ->orderBy('campaigns.created_at', 'desc')
      ->paginate(10);

    return inertia('Campaigns/Index', [
      'campaigns' => $campaigns,
    ]);
  }
}
