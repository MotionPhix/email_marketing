<?php

namespace App\Modules\Lists\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Lists\Models\MailingList;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MailingListController extends Controller
{
  public function index()
  {
    $lists = MailingList::where('user_id', auth()->id())
      ->withCount('activeRecipients')
      ->with(['campaigns' => function ($query) {
        $query->latest()->take(5);
      }])
      ->get();

    return inertia('Lists/Index', [
      'lists' => $lists
    ]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'description' => ['nullable', 'string'],
      'is_default' => ['boolean'],
      'metadata' => ['nullable', 'array']
    ]);

    $list = MailingList::create([
      'user_id' => auth()->id(),
      ...$validated
    ]);

    return redirect()->route('lists.show', $list)
      ->with('success', 'Mailing list created successfully.');
  }

  public function update(Request $request, MailingList $list)
  {
    $this->authorize('update', $list);

    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'description' => ['nullable', 'string'],
      'is_default' => ['boolean'],
      'metadata' => ['nullable', 'array']
    ]);

    $list->update($validated);

    return back()->with('success', 'Mailing list updated successfully.');
  }

  public function destroy(MailingList $list)
  {
    $this->authorize('delete', $list);

    if ($list->campaigns()->exists()) {
      return back()->with('error', 'Cannot delete list with associated campaigns.');
    }

    $list->delete();

    return redirect()->route('lists.index')
      ->with('success', 'Mailing list deleted successfully.');
  }

  public function addRecipients(Request $request, MailingList $list)
  {
    $this->authorize('update', $list);

    $validated = $request->validate([
      'recipient_ids' => ['required', 'array'],
      'recipient_ids.*' => ['exists:recipients,id']
    ]);

    foreach ($validated['recipient_ids'] as $recipientId) {
      $list->subscribe($recipientId);
    }

    return back()->with('success', 'Recipients added successfully.');
  }

  public function removeRecipients(Request $request, MailingList $list)
  {
    $this->authorize('update', $list);

    $validated = $request->validate([
      'recipient_ids' => ['required', 'array'],
      'recipient_ids.*' => ['exists:recipients,id'],
      'reason' => ['nullable', 'string']
    ]);

    foreach ($validated['recipient_ids'] as $recipientId) {
      $list->unsubscribe($recipientId, $validated['reason'] ?? null);
    }

    return back()->with('success', 'Recipients removed successfully.');
  }
}
