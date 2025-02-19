<?php

namespace App\Http\Controllers;

use App\Exports\CampaignsExport;
use App\Http\Filters\CampaignFilter;
use App\Models\Campaign;
use App\Models\EmailTemplate;
use App\Services\CampaignService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class CampaignController extends Controller
{
  public function __construct(protected CampaignService $campaignService)
  {}

  public function index(Request $request)
  {
    $filter = new CampaignFilter($request);

    $campaigns = Campaign::select([
      'id',
      'name',
      'subject',
      'status',
      'created_at',
      'scheduled_at',
      'sent_at',
    ])
      ->withCount('events as recipient_count')
      ->when(auth()->user()->currentTeam, function ($query, $team) {
        $query->where('team_id', $team->id);
      })
      ->tap(fn($query) => $filter->apply($query))
      ->paginate($request->per_page ?? 10)
      ->withQueryString();

    return Inertia::render('Campaigns/Index', [
      'campaigns' => $campaigns,
      'filters' => $filter->getFilters()
    ]);
  }

  public function create()
  {
    return Inertia::render('Campaigns/Form', [
      'templates' => EmailTemplate::all(),
      'campaign' => new Campaign()
    ]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'subject' => 'required|string|max:255',
      'from_name' => 'required|string|max:255',
      'from_email' => 'required|email',
      'reply_to' => 'nullable|email',
      'content' => 'required|string',
      'template_id' => 'nullable|exists:templates,id',
      'scheduled_at' => 'nullable|date|after:now',
      'recipients' => 'required|array',
      'settings' => 'nullable|array',
    ]);

    $campaign = $this->campaignService->create($validated);

    return redirect()->route('campaigns.edit', $campaign)
      ->with('success', 'Index created successfully.');
  }

  public function show(Campaign $campaign)
  {
    $campaign->load([
      'stats',
      'template',
      'user',
      'team',
      'events' => fn($query) => $query->latest()->limit(100)
    ]);

    $eventStats = $this->campaignService->getDetailedStats($campaign);

    return Inertia::render('Campaigns/Show', [
      'campaign' => $campaign,
      'stats' => $eventStats,
      'chartData' => [
        'opens' => $this->campaignService->getOpenRateOverTime($campaign),
        'clicks' => $this->campaignService->getClickRateOverTime($campaign),
        'engagement' => $this->campaignService->getEngagementMetrics($campaign),
      ]
    ]);
  }

  public function edit(Campaign $campaign)
  {
    $campaign->load('template');

    return Inertia::render('Campaigns/Form', [
      'campaign' => $campaign,
      'templates' => EmailTemplate::all()
    ]);
  }

  public function update(Request $request, Campaign $campaign)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'subject' => 'required|string|max:255',
      'from_name' => 'required|string|max:255',
      'from_email' => 'required|email',
      'reply_to' => 'nullable|email',
      'content' => 'required|string',
      'template_id' => 'nullable|exists:templates,id',
      'scheduled_at' => 'nullable|date|after:now',
      'recipients' => 'required|array',
      'settings' => 'nullable|array',
    ]);

    $this->campaignService->update($campaign, $validated);

    return redirect()->route('campaigns.edit', $campaign)
      ->with('success', 'Index updated successfully.');
  }

  public function destroy(Campaign $campaign)
  {
    $this->campaignService->delete($campaign);

    return redirect()->route('campaigns.index')
      ->with('success', 'Index deleted successfully.');
  }

  public function schedule(Request $request, Campaign $campaign)
  {
    $validated = $request->validate([
      'scheduled_at' => 'required|date|after:now',
      'timezone' => 'required|string'
    ]);

    $this->campaignService->schedule($campaign, $validated['scheduled_at'], $validated['timezone']);

    return redirect()->route('campaigns.show', $campaign)
      ->with('success', 'Index scheduled successfully.');
  }

  public function send(Campaign $campaign)
  {
    $this->campaignService->sendCampaign($campaign);

    return redirect()->route('campaigns.show', $campaign)
      ->with('success', 'Index sending has been initiated.');
  }

  public function duplicate(Campaign $campaign)
  {
    $newCampaign = $this->campaignService->duplicate($campaign);

    return redirect()->route('campaigns.edit', $newCampaign)
      ->with('success', 'Index duplicated successfully.');
  }

  public function preview(Campaign $campaign)
  {
    return Inertia::render('Campaigns/Preview', [
      'campaign' => $campaign->load('template')
    ]);
  }

  public function stats(Campaign $campaign)
  {
    $stats = $this->campaignService->getDetailedStats($campaign);

    return Inertia::render('Campaigns/Stats', [
      'campaign' => $campaign,
      'stats' => $stats
    ]);
  }

  public function bulkDelete(Request $request)
  {
    $validated = $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:campaigns,id'
    ]);

    Campaign::whereIn('id', $validated['ids'])->delete();

    return back()->with('success', 'Selected campaigns have been deleted.');
  }

  public function export(Request $request)
  {
    return Excel::download(
      new CampaignsExport($request->all()),
      'campaigns.xlsx'
    );
  }
}
