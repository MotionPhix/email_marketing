<?php

namespace App\Http\Controllers\Audience;

use App\Http\Controllers\Controller;
use App\Models\Audience;
use Illuminate\Http\Request;

class Index extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    $audiences = Audience::with('recipients')->get();

    return inertia('Audiences/Index', [
      'audiences' => $audiences,
    ]);
  }
}
