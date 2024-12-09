<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\Recipient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Store extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    $validated = $request->validate([
      'email' => [
        'required', 'email',
        Rule::unique('recipients')
          ->where(fn($query) => $query->where('user_id', $request->user()->id)),
      ],
      'gender' => 'required|in:male,female,unspecified',
      'status' => 'required|in:active,inactive,banned,unsubscribed',
      'name' => 'required',
    ], [
      'email.required' => 'Provide an email for the recipient',
      'email.email' => 'Please enter a valid email address',
      'email.unique' => 'You have a recipient with this email',
      'gender.required' => 'Specify a gender for the recipient',
      'status.required' => 'Set a status for the recipient',
      'name.required' => 'Provide a name for the recipient'
    ]);

    $validated['user_id'] = auth()->user()->id;

    Recipient::create($validated);

    return back();
  }
}
