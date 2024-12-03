<?php

namespace App\Http\Controllers\Template;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;

class Trash extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Template $template)
  {
    $template->delete();

    return back();
  }
}
