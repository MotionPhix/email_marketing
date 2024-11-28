<?php

namespace App\Http\Controllers\Audience;

use App\Http\Controllers\Controller;
use App\Models\Audience;
use App\Models\Recipient;
use Illuminate\Http\Request;

class RemoveRecipient extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Audience $audience, Recipient $recipient)
  {
    // Ensure the authenticated user owns the audience
    if ($audience->user_id !== $request->user()->id) {

      return back()->with('flash', [
        'bannerStyle' => 'danger',
        'banner' => 'You are not allowed to change recipients!',
      ]);

    }

    // Detach the recipient from the audience
    $audience->recipients()->detach($recipient->id);

    return redirect()->back()
      ->with('flash', [
        'bannerStyle' => 'success',
        'banner' => 'Recipient removed from the audience successfully.',
      ]);
  }
}
