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
  public function __invoke(Request $request, Campaign $campaign, Template $template = null)
  {
    return Inertia('Templates/Builder', [
      'fullDesign' => fn() => $template ?: new Template(['design' => '', 'content' => '']),
      'campaign' => $campaign
    ]);
  }
}
