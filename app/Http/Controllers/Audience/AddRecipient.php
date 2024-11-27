<?php

namespace App\Http\Controllers\Audience;

use App\Http\Controllers\Controller;
use App\Models\Audience;
use Illuminate\Http\Request;

class AddRecipient extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Audience $audience)
  {
    if (!count($request->user()->recipients))
      return back()->with('flash', [
        'bannerStyle' => 'danger',
        'banner' => 'You don\'t have recipients, yet. Add recipients',
      ]);

    return Inertia('Audiences/AddRecipient', [
      'audience' => $audience->only(['id', 'uuid', 'name']),
      'recipients' => fn() => $request->user()->recipients
    ]);
  }
}
