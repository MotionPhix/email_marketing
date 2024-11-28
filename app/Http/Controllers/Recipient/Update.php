<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\Recipient;
use Illuminate\Http\Request;

class Update extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Recipient $recipient)
  {
    if($request->user()->id !== $recipient->user_id) {

      return back()->with('flash', [
        'bannerStyle' => 'danger',
        'banner' => 'You are not allowed to update this recipient!',
      ]);

    }

    $recipient->update($request->only(['email', 'name']));

    return redirect()->back();
  }
}
