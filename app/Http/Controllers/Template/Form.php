<?php

namespace App\Http\Controllers\Template;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Template;
use Illuminate\Http\Request;

class Form extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, ?Campaign $campaign, ?Template $template)
  {
    return Inertia('Templates/Builder', [
      'fullDesign' => $template ? [
        'id' => $template->id,
        'uuid' => $template->uuid,
        'name' => $template->name,
        'mode' => $template->mode,
        'design' => $template->design,
        'content' => $template->content,
      ] : [
        'design' => '{}', // Return empty JSON object if no template
        'content' => '',
      ],
      'campaign' => $campaign ?: new Campaign()
    ]);
  }
}
