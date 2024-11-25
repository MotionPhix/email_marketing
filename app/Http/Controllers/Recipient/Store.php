<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\Recipient;
use Illuminate\Http\Request;

class Store extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    $validated = $request->validate([
      'audience_id' => 'required|exists:audiences,id',
      'email' => 'required|email|unique:recipients,email',
      'name' => 'nullable|string|max:255',
    ]);

    Recipient::create($validated);

    return redirect()
      ->route('audiences.show', $validated['audience_id'])
      ->with('success', 'Recipient added successfully!');
  }
}
