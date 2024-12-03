<?php

namespace App\Http\Controllers\Template;

use App\Http\Controllers\Controller;
use App\Models\Template;

class Index extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke()
  {
    return inertia('Templates/Index', [
      'templates' => fn() => Template::userAndSystem(auth()->id())->get(),
    ]);
  }
}
