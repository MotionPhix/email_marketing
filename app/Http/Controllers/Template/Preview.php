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
  public function __invoke(Request $request)
  {
    $request->validate([
      'template_id' => 'required|exists:templates,id',
      'audience_id' => 'required|exists:audiences,id',
    ]);

    $template = Template::findOrFail($request->template_id);
    $audience = Audience::findOrFail($request->audience_id);

    // Sample dynamic data
    $data = [
      'name' => $audience->name,
      'email' => $audience->email,
      'unsubscribe_link' => route('unsubscribe', ['email' => $audience->email]),
    ];

    // Render template
    $renderedHtml = TemplateRenderer::render($template->content, $data);

    return response()->json(['html' => $renderedHtml]);
  }
}
