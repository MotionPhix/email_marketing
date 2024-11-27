<?php

namespace App\Http\Controllers\Template;

use App\Http\Controllers\Controller;
use App\Models\Audience;
use App\Models\Template;
use App\Services\TemplateRenderer;
use Illuminate\Http\Request;

class Preview extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Template $template)
  {
    return Inertia('Templates/Preview', [
      'content' => fn() => $template->content
    ]);
  }
}
