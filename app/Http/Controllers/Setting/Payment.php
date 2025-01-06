<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Setting;
use Illuminate\Http\Request;

class Payment extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Plan $plan, Setting $setting)
  {
    $validatedData = $request->validate([
      'email_from_address' => 'sometimes|email',
      'email_from_name' => 'sometimes|min:4|max:255',
      'sender_name' => 'sometimes|min:4|max:255',
      'timezone' => 'required|string|max:50',
      'plan_id' => 'required|exists:plans,id'
    ]);

    $setting->update($validatedData);

    return back();
  }
}
