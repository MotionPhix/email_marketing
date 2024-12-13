<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Subscription;
use Illuminate\Http\Request;

class Setup extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    return Inertia('Settings', [
      'settings' => fn() => Setting::where('user_id', $request->user()->id)->with('subscription')->first(),
      'subscriptions' => fn() => Subscription::all()
    ]);
  }
}
