<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Template;
use Illuminate\Http\Request;

class Assign extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Campaign $campaign, Template $template)
  {
    $campaign->update([
      'template_id' => $template->id,
    ]);

    return redirect()->route('campaigns.create', ['template_created' => $template->id]);
  }
}
