<?php

namespace App\Http\Controllers\Audience;

use App\Http\Controllers\Controller;
use App\Models\Audience;
use Illuminate\Http\Request;

class Form extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Audience $audience)
  {
    return Inertia('Audiences/Form', [
      'audience' => $audience ?: new Audience()
    ]);
  }
}
