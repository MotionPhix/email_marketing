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
    $query = Recipient::where('user_id', $request->user()->id)->orderBy('name');

    // Apply search
    if ($search = $request->input('search')) {
      $query->where('name', 'like', "%{$search}%")
        ->orWhere('email', 'like', "%{$search}%")->orderBy('email');
    }

    // Apply filters
    if ($status = $request->input('status')) {
      $query->where('status', $status)->orderBy('status');
    }

    if ($status = $request->input('gender')) {
      $query->where('gender', $status)->orderBy('gender');
    }

    return Inertia('Recipients/Index', [
      'recipients' => $query->paginate(15),
    ]);
  }
}
