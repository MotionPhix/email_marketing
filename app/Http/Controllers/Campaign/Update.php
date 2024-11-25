<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;

class Update extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Campaign $campaign)
  {
    // Ensure the user owns the campaign
    // $this->authorize('update', $campaign);

    $validated = $request->validate([
      'scheduled_at' => 'nullable|date|after:now',
      'title' => 'required|unique:campaigns|max:100',
      'subject' => 'required|max:200',
      'template_id' => 'nullable|exists:templates,uuid',
      'audience_id' => 'nullable|exists:audiences,uuid',
      'description' => 'nullable',
    ]);

    if (
      $request->title !== $campaign->title ||
      $request->scheduled_at !== $campaign->scheduled_at ||
      $request->description !== $campaign->description ||
      $request->subject !== $campaign->subject ||
      $request->template_id !== $campaign->template_id ||
      $request->audience_id !== $campaign->audience_id
    ) {

      $campaign->update([
        'title' => $validated['title'],
        'content' => $validated['content'],
        'status' => $validated['scheduled_at'] ? 'scheduled' : 'draft',
        'scheduled_at' => $validated['scheduled_at'],
      ]);

    }

    return redirect()
      ->route('campaigns.create', $campaign->uuid);
  }
}
