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
    /*$audiences = Audience::where('user_id', $request->user()->id)
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
    ]);*/

    $query = Audience::query()
      ->where('user_id', $request->user()->id)
      ->with(['recipients' => function ($query) {
        $query->select('recipients.id', 'uuid', 'name', 'email')
          ->limit(3);
      }])
      ->withCount('recipients');

    // Handle search
    if ($search = $request->input('search')) {
      $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('description', 'like', "%{$search}%");
      });
    }

    // Handle sorting
    $sortBy = $request->input('sort_by', 'name');
    $sortOrder = $request->input('sort_direction', 'asc');

    $allowedSortFields = ['name', 'recipients_count', 'created_at'];

    if (in_array($sortBy, $allowedSortFields)) {
      if ($sortBy === 'recipients_count') {
        $query->orderBy('recipients_count', $sortOrder);
      } else {
        $query->orderBy($sortBy, $sortOrder);
      }
    }

    $audiences = $query->paginate($request->input('per_page', 10))
      ->through(function ($audience) {
        return [
          'id' => $audience->id,
          'uuid' => $audience->uuid,
          'name' => $audience->name,
          'description' => $audience->description,
          'created_at' => $audience->created_at->format('Y-m-d H:i:s'),
          'recipients' => $audience->recipients->map(fn($recipient) => [
            'id' => $recipient->id,
            'uuid' => $recipient->uuid,
            'name' => $recipient->name,
            'email' => $recipient->email,
          ]),
          'recipients_count' => $audience->recipients_count,
          'remaining_recipients_count' => max(
            0,
            $audience->recipients_count - $audience->recipients->count()
          ),
        ];
      });

    return inertia('Audiences/Index', [
      'audiences' => $audiences,
      'filters' => [
        'search' => $search,
        'sort_by' => $sortBy,
        'sort_direction' => $sortOrder,
      ],
    ]);
  }
}
