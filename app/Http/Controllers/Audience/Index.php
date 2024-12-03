<?php

namespace App\Http\Controllers\Audience;

use App\Http\Controllers\Controller;
use App\Models\Audience;
use Illuminate\Http\Request;

class Index extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    $audiences = Audience::where('user_id', $request->user()->id)
      ->with(['recipients' => function ($query) {
        $query->select('uuid', 'name', 'email')->limit(3);
      }])
      ->withCount('recipients')
      ->paginate(10)
      ->through(function ($audience) {
        return [
          'id' => $audience->id,
          'uuid' => $audience->uuid,
          'name' => $audience->name,
          'description' => $audience->description,
          'recipients' => $audience->recipients,
          'recipients_count' => $audience->recipients_count,
          'remaining_recipients_count' => max(0, $audience->recipients_count - $audience->recipients->count()),
        ];
      });

    return inertia('Audiences/Index', [
      'audiences' => $audiences,
    ]);
  }
}
