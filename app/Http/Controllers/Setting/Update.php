<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class Update extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Setting $setting)
  {
    $validatedData = $request->validate([
      'email_from_address' => 'required|email',
      'email_from_name' => 'required|string|max:255',
      'sender_name' => 'nullable|string|max:255',
      'timezone' => 'required|string|max:50'
    ]);

    $setting->update($validatedData);

    return back();
  }
}
