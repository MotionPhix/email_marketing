<?php

namespace App\Http\Controllers\Template;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Store extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    // Validate the request
    $validated = $request->validate([
      'name' => [
        'required',
        'max:50',
        Rule::unique('templates')->where(
          function ($query) use ($request) {
            return $query->where('user_id', $request->user()->id);
          })
      ], // Name is required and unique for the user
      'design' => ['required'],
      'content' => ['required', 'string'], // HTML content
    ], [
      'name.required' => 'Provide a name for the template',
      'name.unique' => 'You already have a template with this name',
      'design.required' => 'You have to save the design first',
      'content.required' => 'Provide content that we will use in emails',
    ]);

    // Encode the design field as JSON
    $validated['design'] = json_encode($validated);

    // Create a new template
    $template = Template::create([
      'name' => $validated['name'],
      'design' => $validated['design'],
      'content' => $validated['content'],
      'user_id' => $request->user()->id,
    ]);

    $campaign = Campaign::where('id', $request->campaign_id)->first();
    $campaign->update([
      'template_id' => $template->id
    ]);

    // Redirect back to the campaigns with the created template ID
    return redirect()
      ->route('campaigns.create', $campaign->uuid);
  }
}
