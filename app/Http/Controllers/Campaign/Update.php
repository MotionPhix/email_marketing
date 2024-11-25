<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

      'title' => [
        'required',
        'max:100',
        Rule::unique('campaigns')
          ->where(fn($query) => $query->where('user_id', $request->user()->id))
          ->ignore($campaign->id ?? null), // Exclude the current campaign ID if updating
      ],

      'subject' => 'required|max:200',
      'template_id' => 'nullable|exists:templates,id',
      'audience_id' => 'nullable|exists:audiences,id',
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
        'template_id' => $validated['template_id'],
        'audience_id' => $validated['audience_id'],
        'description' => $validated['description'],
        'status' => $validated['scheduled_at'] ? 'scheduled' : 'draft',
        'scheduled_at' => $validated['scheduled_at'],
      ]);
    }

    return redirect()
      ->route('campaigns.create', $campaign->uuid);
  }
}
