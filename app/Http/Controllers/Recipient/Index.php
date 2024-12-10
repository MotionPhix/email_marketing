<?php

namespace App\Http\Controllers\Recipient;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Recipient;
use Illuminate\Http\Request;
use ProtoneMedia\LaravelCrossEloquentSearch\Search;

class Index extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    $query = Recipient::where('user_id', $request->user()->id)->withoutTrashed();

    // Apply search
    if ($search = $request->input('search')) {
      $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%");
      });
    }

    // Apply filters
    if ($status = $request->input('status')) {
      $query->where('status', $status);
    }

    if ($gender = $request->input('gender')) {
      $query->where('gender', $gender);
    }

    // Default sorting
    $query->orderBy('name');

    return Inertia('Recipients/Index', [
      'recipients' => $query->paginate(7)->withQueryString(), // Preserve query string
    ]);
  }
}
