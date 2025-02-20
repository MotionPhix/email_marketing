<?php

namespace App\Http\Controllers;

use App\Models\MailingList;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MailingListController extends Controller
{
  use AuthorizesRequests;

  public function index(Request $request)
  {
    $lists = MailingList::forTeam($request->user()->currentTeam)
      ->withCount('subscribers')
      ->latest()
      ->get()
      ->map(fn($list) => [
        'id' => $list->id,
        'name' => $list->name,
        'description' => $list->description,
        'subscriberCount' => $list->subscribers_count,
        'is_default' => $list->is_default,
        'created_at' => $list->created_at
      ]);

    return Inertia::render('MailingLists/Index', [
      'lists' => $lists
    ]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'description' => ['nullable', 'string'],
      'is_default' => ['boolean'],
      'settings' => ['nullable', 'array']
    ]);

    $list = MailingList::create(array_merge($validated, [
      'team_id' => $request->user()->currentTeam->id
    ]));

    return back()->with('success', 'Mailing list created successfully.');
  }

  public function update(Request $request, MailingList $mailingList)
  {
    $this->authorize('update', $mailingList);

    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'description' => ['nullable', 'string'],
      'is_default' => ['boolean'],
      'settings' => ['nullable', 'array']
    ]);

    $mailingList->update($validated);

    return back()->with('success', 'Mailing list updated successfully.');
  }

  public function destroy(MailingList $mailingList)
  {
    $this->authorize('delete', $mailingList);

    $mailingList->delete();

    return back()->with('success', 'Mailing list deleted successfully.');
  }

  public function getSubscribers(MailingList $mailingList)
  {
    $this->authorize('view', $mailingList);

    return $mailingList->subscribers()
      ->select(['id', 'email', 'first_name', 'last_name'])
      ->paginate();
  }
}
