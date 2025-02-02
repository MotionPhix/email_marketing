<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Index extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    /*$campaigns = DB::table('campaigns')
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
    ]);*/

    $query = DB::table('campaigns')
      ->select(
        'campaigns.id',
        'campaigns.uuid',
        'campaigns.title',
        'campaigns.subject',
        'campaigns.template_id',
        'campaigns.scheduled_at',
        'campaigns.status',
        'campaigns.audience_id',
        'campaigns.created_at',
        'audiences.name as audience_name',
        DB::raw('
          (SELECT COUNT(*)
           FROM audience_recipient
           WHERE audience_recipient.audience_id = campaigns.audience_id
          ) as recipients_count
        '),
        DB::raw('
          (SELECT COUNT(*)
           FROM email_logs
           WHERE email_logs.campaign_uuid = campaigns.uuid
          ) as emails_sent
        ')
      )
      ->leftJoin('audiences', 'campaigns.audience_id', '=', 'audiences.id')
      ->where('campaigns.user_id', auth()->id());

    // Handle search
    if ($search = $request->input('search')) {
      $query->where(function ($q) use ($search) {
        $q->where('campaigns.title', 'like', "%{$search}%")
          ->orWhere('audiences.name', 'like', "%{$search}%")
          ->orWhere('campaigns.subject', 'like', "%{$search}%");
      });
    }

    // Handle status filtering
    if ($status = $request->input('status')) {
      $query->whereIn('campaigns.status', is_array($status) ? $status : [$status]);
    }

    // Handle date range filtering
    if ($dateRange = $request->input('date_range')) {
      $dates = explode(',', $dateRange);
      if (count($dates) === 2) {
        $query->whereBetween('campaigns.created_at', [
          Carbon::parse($dates[0])->startOfDay(),
          Carbon::parse($dates[1])->endOfDay()
        ]);
      }
    }

    // Handle sorting
    $sortField = $request->input('sort_by', 'created_at');
    $sortDirection = $request->input('sort_direction', 'desc');

    $allowedSortFields = [
      'title' => 'campaigns.title',
      'status' => 'campaigns.status',
      'scheduled_at' => 'campaigns.scheduled_at',
      'created_at' => 'campaigns.created_at',
      'audience' => 'audiences.name',
      'recipients_count' => 'recipients_count'
    ];

    if (isset($allowedSortFields[$sortField])) {
      $query->orderBy($allowedSortFields[$sortField], $sortDirection);
    }

    $campaigns = $query->paginate($request->input('per_page', 10));

    // Get campaign statistics
    $stats = [
      'total' => DB::table('campaigns')->where('user_id', auth()->id())->count(),
      'draft' => DB::table('campaigns')->where('user_id', auth()->id())->where('status', Campaign::STATUS_DRAFT)->count(),
      'scheduled' => DB::table('campaigns')->where('user_id', auth()->id())->where('status', Campaign::STATUS_SCHEDULED)->count(),
      'sent' => DB::table('campaigns')->where('user_id', auth()->id())->where('status', Campaign::STATUS_SENT)->count(),
      'failed' => DB::table('campaigns')->where('user_id', auth()->id())->where('status', Campaign::STATUS_FAILED)->count(),
    ];

    return inertia('Campaigns/Index', [
      'campaigns' => $campaigns,
      'stats' => $stats,
      'filters' => [
        'search' => $search,
        'status' => $status,
        'date_range' => $dateRange,
        'sort' => [
          'field' => $sortField,
          'direction' => $sortDirection
        ]
      ]
    ]);

  }
}
