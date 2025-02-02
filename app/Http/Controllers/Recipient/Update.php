<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\Recipient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    $validated = $request->validate([
      'email' => [
        'required', 'email:dns,rfc',
        Rule::unique('recipients')
          ->where(fn($query) => $query->where('user_id', $request->user()->id))
          ->ignore($recipient->id ?? null),
      ],
      'gender' => 'nullable|in:male,female,unspecified',
      'name' => 'required',
    ], [
      'email.required' => 'Provide an email for the recipient',
      'email.email' => 'Please enter a valid email address',
      'email.unique' => 'You have a recipient with this email',
      'name.required' => 'Provide a name for the recipient'
    ]);

    $recipient->update($validated);

    return redirect()->back();
  }
}
