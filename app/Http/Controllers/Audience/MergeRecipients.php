<?php

namespace App\Http\Controllers\Audience;

use App\Http\Controllers\Controller;
use App\Models\Audience;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class MergeRecipients extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Audience $audience)
  {
    $validated = $request->validate([
      'selectedRecipients' => ['required', 'array'],
      'selectedRecipients.*.id' => ['required', 'integer', Rule::exists('recipients', 'id')],
      'selectedRecipients.*.uuid' => ['required', 'uuid', Rule::exists('recipients', 'uuid')],
      'selectedRecipients.*.name' => ['required', 'string', 'max:255'],
      'selectedRecipients.*.email' => ['required', 'email'],
    ], [
      'selectedRecipients.required' => 'You must select at least one recipient.',
      'selectedRecipients.array' => 'The recipients data must be an array.',
      'selectedRecipients.*.id.required' => 'Each recipient must have an ID.',
      'selectedRecipients.*.id.integer' => 'Each recipient ID must be an integer.',
      'selectedRecipients.*.id.exists' => 'The selected recipient does not exist.',
      'selectedRecipients.*.uuid.required' => 'Each recipient must have a UUID.',
      'selectedRecipients.*.uuid.uuid' => 'The UUID format is invalid.',
      'selectedRecipients.*.uuid.exists' => 'The selected recipient does not exist.',
      'selectedRecipients.*.name.required' => 'Each recipient must have a name.',
      'selectedRecipients.*.name.string' => 'The name must be a string.',
      'selectedRecipients.*.name.max' => 'The name must not exceed 255 characters.',
      'selectedRecipients.*.email.required' => 'Each recipient must have an email address.',
      'selectedRecipients.*.email.email' => 'The email address format is invalid.',
    ]);

    // Sync the recipients with the audience
    $recipientIds = array_column($validated['selectedRecipients'], 'id');
    $audience->recipients()->sync($recipientIds);

    return redirect()->back()->with('flash', [
      'bannerStyle' => 'success',
      'banner' => 'Recipients updated successfully.',
    ]);
  }
}
