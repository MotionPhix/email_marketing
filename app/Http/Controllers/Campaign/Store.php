<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Audience;
use App\Models\Campaign;
use App\Models\Template;
use Illuminate\Http\Request;

class Store extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    $validated = $request->validate([
      'title' => 'required|unique:campaigns|max:100',
      'subject' => 'required|max:200',
      'scheduled_at' => 'nullable|date|after:now',
      'template_id' => 'nullable|exists:templates,uuid',
      'audience_id' => 'nullable|exists:audiences,uuid',
      'description' => 'nullable',
    ], [
      'title.required' => 'Provide a name for the campaign',
      'title.max' => 'Campaign name is too long',
      'subject.required' => 'Enter a campaign subject'
    ]);

    if ($request->template_id && filled($validated['template_id'])) {
      $validated['template_id'] = fn() => Template::where('uuid', $validated['template_id'])->value('id');
    }

    if ($request->audience_id && filled($validated['audience_id'])) {
      $validated['audience_id'] = fn() => Audience::where('uuid', $validated['audience_id'])->value('id');
    }

    $data = array_filter([
      'title' => $validated['title'] ?? '',
      'subject' => $validated['subject'] ?? '',
      'description' => $validated['description'] ?? '',
      'template_id' => $validated['template_id'] ?? null,
      'status' => $request->scheduled_at ? 'scheduled' : 'draft',
      'audience_id' => $validated['audience_id'] ?? null,
      'scheduled_at' => $validated['scheduled_at'] ?? null,
      'user_id' => $request->user()->id,
    ], fn($value) => !is_null($value) && $value !== ''); // Remove null values

    $campaign = Campaign::create($data);

    // Determine response context
    if ($request->expectsJson()) {
      return response()->json([
        'message' => 'Campaign created successfully!',
        'campaign' => $campaign,
      ]);
    }

    // Redirect for traditional form submissions
    return redirect()->route('campaigns.create', $campaign->uuid);
  }
}
