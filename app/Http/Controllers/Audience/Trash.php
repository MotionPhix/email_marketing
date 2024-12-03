<?php

namespace App\Http\Controllers\Audience;

use App\Http\Controllers\Controller;
use App\Models\Audience;
use Illuminate\Http\Request;

class Trash extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Audience $audience)
  {
    // Ensure the user owns the audience
    if ($audience->user_id !== $request->user()->id) {
      abort(403, 'Unauthorized action.');
    }

    // Check if campaigns are linked
    if ($audience->campaigns()->exists()) {
      return back()->withErrors(
        'Cannot delete an audience with active campaigns.',
        'flash',
      );
    }

    // Delete the audience and associated recipients (cascade deletes handled by DB)
    $audience->delete();
  }
}
