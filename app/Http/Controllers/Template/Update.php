<?php

namespace App\Http\Controllers\Template;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Update extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Template $template)
  {
    $validated = $request->validate([
      'name' => [
        'required',
        'max:50',
        Rule::unique('templates')
          ->ignore($template->id)
          ->where(function ($query) use ($request) {
          return $query->where('user_id', $request->user()->id);
        }),
      ],
      'design' => ['required', 'array'],
      'content' => ['required', 'string'],
    ], [
      'name.required' => 'Provide a name for the template',
      'name.unique' => 'You already have a template with this name',
      'design.required' => 'You have to save the design first',
      'design.array' => 'The design format isn\'t correct',
      'content.required' => 'Provide content for emails',
    ]);

    $validated['design'] = json_encode($validated['design']);

    $template->update($validated);

    return redirect()->back();
  }
}
