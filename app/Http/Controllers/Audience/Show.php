<?php

namespace App\Http\Controllers\Audience;

use App\Http\Controllers\Controller;
use App\Models\Audience;

class Show extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Audience $audience)
  {
    return Inertia('Audiences/Show', [
      'audience' => $audience->load('recipients', 'campaigns'),
    ]);
  }
}
