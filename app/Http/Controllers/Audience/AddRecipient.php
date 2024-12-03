<?php

namespace App\Http\Controllers\Audience;

use App\Http\Controllers\Controller;
use App\Models\Audience;
use Illuminate\Http\Request;

class AddRecipient extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Audience $audience)
  {
    if (! $request->user()->recipients()->exists())
      return back()->with('flash', [
        'bannerStyle' => 'danger',
        'banner' => 'You don\'t have recipients, yet. Add recipients',
      ]);

    $audience->load('recipients');

    return Inertia('Audiences/AddRecipient', [
      'audience' => [
        'id' => $audience->id,
        'uuid' => $audience->uuid,
        'name' => $audience->name,
        'recipients' => $audience->recipients->map(fn ($recipient) => [
          'id' => $recipient->id,
          'uuid' => $recipient->uuid,
          'name' => $recipient->name,
          'email' => $recipient->email,
        ]),
      ],
      'recipients' => fn() => $request->user()->recipients->map(fn($recipient) => $recipient->only('id', 'uuid', 'name', 'email')),
    ]);
  }
}
