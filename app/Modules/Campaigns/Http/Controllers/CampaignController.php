<?php

namespace App\Modules\Campaigns\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Campaigns\Jobs\ProcessCampaignQueue;
use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Campaigns\Services\CampaignService;
use App\Modules\Lists\Models\MailingList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class CampaignController extends Controller
{
  public function __construct(
    private readonly CampaignService $campaigns
  ) {}

  public function index(): Response
  {
    $campaigns = Campaign::query()
      ->where('user_id', Auth::id())
      ->with('lists')
      ->latest()
      ->paginate(10);

    return inertia('Campaigns/Index', [
      'campaigns' => $campaigns,
      'stats' => [
        'total' => Campaign::where('user_id', Auth::id())->count(),
        'sent' => Campaign::where('user_id', Auth::id())
          ->where('status', Campaign::STATUS_SENT)
          ->count(),
        'scheduled' => Campaign::where('user_id', Auth::id())
          ->where('status', Campaign::STATUS_SCHEDULED)
          ->count(),
        'draft' => Campaign::where('user_id', Auth::id())
          ->where('status', Campaign::STATUS_DRAFT)
          ->count(),
      ],
    ]);
  }

  public function create(): Response
  {
    $lists = MailingList::where('user_id', Auth::id())->get();

    return inertia('Campaigns/Create', [
      'lists' => $lists,
      'defaultFromName' => Auth::user()->name,
      'defaultFromEmail' => Auth::user()->email,
    ]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'subject' => ['required', 'string', 'max:255'],
      'content' => ['required', 'string'],
      'template_data' => ['nullable', 'array'],
      'from_name' => ['nullable', 'string', 'max:255'],
      'from_email' => ['nullable', 'email', 'max:255'],
      'reply_to' => ['nullable', 'email', 'max:255'],
      'scheduled_at' => ['nullable', 'date', 'after:now'],
      'list_ids' => ['required', 'array', 'min:1'],
      'list_ids.*' => ['exists:mailing_lists,id'],
    ]);

    $campaign = $this->campaigns->create($validated);

    return redirect()
      ->route('campaigns.edit', $campaign)
      ->with('success', 'Campaign created successfully.');
  }

  public function edit(Campaign $campaign): Response
  {
    $this->authorize('update', $campaign);

    $lists = MailingList::where('user_id', Auth::id())->get();

    return inertia('Campaigns/Edit', [
      'campaign' => $campaign->load('lists'),
      'lists' => $lists,
    ]);
  }

  public function update(Request $request, Campaign $campaign)
  {
    $this->authorize('update', $campaign);

    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'subject' => ['required', 'string', 'max:255'],
      'content' => ['required', 'string'],
      'template_data' => ['nullable', 'array'],
      'from_name' => ['nullable', 'string', 'max:255'],
      'from_email' => ['nullable', 'email', 'max:255'],
      'reply_to' => ['nullable', 'email', 'max:255'],
      'scheduled_at' => ['nullable', 'date', 'after:now'],
      'list_ids' => ['required', 'array', 'min:1'],
      'list_ids.*' => ['exists:mailing_lists,id'],
    ]);

    $this->campaigns->update($campaign, $validated);

    return redirect()
      ->back()
      ->with('success', 'Campaign updated successfully.');
  }

  public function schedule(Request $request, Campaign $campaign)
  {
    $request->validate([
      'scheduled_at' => ['required', 'date', 'after:now'],
    ]);

    $campaign->update([
      'status' => Campaign::STATUS_SCHEDULED,
      'scheduled_at' => $request->scheduled_at,
    ]);

    ProcessCampaignQueue::dispatch($campaign)
      ->delay($request->scheduled_at);

    return back()->with('success', 'Campaign scheduled successfully.');
  }

  public function schedule(Request $request, Campaign $campaign)
  {
    $this->authorize('update', $campaign);

    $validated = $request->validate([
      'scheduled_at' => ['required', 'date', 'after:now'],
    ]);

    $this->campaigns->schedule($campaign, $validated['scheduled_at']);

    return redirect()
      ->back()
      ->with('success', 'Campaign scheduled successfully.');
  }

  public function cancel(Campaign $campaign)
  {
    $this->authorize('update', $campaign);

    $this->campaigns->cancel($campaign);

    return redirect()
      ->back()
      ->with('success', 'Campaign cancelled successfully.');
  }

  public function destroy(Campaign $campaign)
  {
    $this->authorize('delete', $campaign);

    $this->campaigns->delete($campaign);

    return redirect()
      ->route('campaigns.index')
      ->with('success', 'Campaign deleted successfully.');
  }

  public function preview(Campaign $campaign): Response
  {
    $this->authorize('view', $campaign);

    return inertia('Campaigns/Preview', [
      'campaign' => $campaign->load('lists'),
    ]);
  }

  public function sendNow(Campaign $campaign)
  {
    if (!$campaign->isDraft()) {
      return back()->with('error', 'Campaign cannot be sent.');
    }

    ProcessCampaignQueue::dispatch($campaign);

    return back()->with('success', 'Campaign queued for sending.');
  }

  public function sendTest(Request $request, Campaign $campaign)
  {
    $this->authorize('view', $campaign);

    $request->validate([
      'email' => ['required', 'email'],
    ]);

    // You'll need to implement this method in your CampaignService
    $this->campaigns->sendTest($campaign, $request->email);

    return response()->json([
      'message' => 'Test email sent successfully',
    ]);
  }
}
