<?php

namespace App\Http\Controllers\Template;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use function Pest\Laravel\json;

class Store extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, ?Campaign $campaign = null)
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
      'design' => ['required', 'array'],
      'content' => ['required', 'string'],
      'mode' => ['required', 'string'],
    ], [
      'name.required' => 'Provide a name for the template',
      'name.unique' => 'You already have a template with this name',
      'design.required' => 'You have to save the design first',
      'content.required' => 'Provide content that we will use in emails',
      'mode.required' => 'Set a mode for the template',
    ]);

    // Create a new template
    $template = Template::create([
      'name' => $validated['name'],
      'design' => json_encode($validated['design'], true),
      'content' => $validated['content'],
      'mode' => $validated['mode'],
      'user_id' => $request->user()->id,
    ]);

    if ($campaign) {

      $campaign = Campaign::where('id', $request->campaign_id)->first();

      $campaign->update([
        'template_id' => $template->id
      ]);

    }

    return redirect()->back();
  }
}
