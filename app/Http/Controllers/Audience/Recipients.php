<?php

namespace App\Http\Controllers\Audience;

use App\Http\Controllers\Controller;
use App\Models\Audience;
use App\Models\Recipient;
use Illuminate\Http\Request;

class Recipients extends Controller
{
  public function remove(Audience $audience, Recipient $recipient)
  {
    $audience->recipients()->detach($recipient->id);
    return response()->json(['message' => 'Recipient removed successfully.']);
  }

  public function add(Request $request, Audience $audience)
  {
    $request->validate([
      'recipients' => 'required|array',
      'recipients.*.email' => 'required|email|unique:recipients,email',
      'recipients.*.name' => 'required|string|max:255',
    ]);

    $audience->recipients()->syncWithoutDetaching($request->recipients);

    return response()->json([
      'message' => 'Recipients added successfully.'
    ]);
  }
}
