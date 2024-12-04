<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\Recipient;
use Illuminate\Http\Request;

class Index extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    $query = Recipient::where('user_id', $request->user()->id);

    // Apply search
    if ($search = $request->input('search')) {
      $query->where('name', 'like', "%{$search}%")
        ->orWhere('email', 'like', "%{$search}%");
    }

    // Apply filters
    if ($status = $request->input('status')) {
      $query->where('gender', $status);
    }

    return Inertia('Recipients/Index', [
      'recipients' => $query->paginate(15),
    ]);
  }
}
