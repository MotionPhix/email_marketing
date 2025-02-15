<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CampaignController extends Controller
{
  public function index()
  {
    $campaigns = Campaign::with('stats')
      ->latest()
      ->paginate(10);

    return Inertia::render('Campaigns/Index', [
      'campaigns' => $campaigns
    ]);
  }

  public function create()
  {
    return Inertia::render('Campaigns/Form', [
      'templates' => Template::all()
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

    $campaign = Campaign::create($validated);

    return redirect()->route('campaigns.edit', $campaign)
      ->with('success', 'Campaign created successfully.');
  }
}
