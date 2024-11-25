<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Template;
use Illuminate\Http\Request;

class Form extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Campaign $campaign = null)
  {
    return Inertia('Campaigns/Form', [
      'templates' => fn() => Template::userAndSystem(auth()->id())
        ->get()->transform(fn($template) => [
          'value' => $template->id,
          'label' => $template->name,
          'description' => $template->description,
          'preview' => $template->content,
        ]),
      'audiences' => fn() => $request->user()
        ->audiences()
        ->select('id','uuid','name')
        ->with('recipients:id,audience_id,name,email') // Eager load only necessary columns
        ->get(),
      'campaign' => fn() => $campaign ?: new Campaign()
    ]);
  }
}
