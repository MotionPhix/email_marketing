<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class Setup extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    return Inertia('Settings', [
      'settings' => fn() => Setting::where('user_id', $request->user()->id)
    ]);
  }
}
