<?php

namespace App\Modules\Segments\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Segments\Models\Segment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SegmentController extends Controller
{
  public function index()
  {
    $segments = Segment::where('user_id', auth()->id())
      ->withCount('recipients')
      ->latest()
      ->get();

    return inertia('Segments/Index', [
      'segments' => $segments
    ]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'description' => ['nullable', 'string'],
      'conditions' => ['required', 'array'],
      'conditions.*.type' => ['required', Rule::in(['field', 'activity', 'campaign', 'list'])],
      'match_type' => ['required', Rule::in(['all', 'any'])],
      'metadata' => ['nullable', 'array']
    ]);

    $segment = Segment::create([
      'user_id' => auth()->id(),
      ...$validated
    ]);

    // Apply conditions immediately
    $segment->updateRecipients();

    return redirect()->route('segments.show', $segment)
      ->with('success', 'Segment created successfully.');
  }

  public function show(Segment $segment)
  {
    $this->authorize('view', $segment);

    $segment->load('recipients');

    return inertia('Segments/Show', [
      'segment' => $segment,
      'recipientCount' => $segment->recipients()->count(),
      'lastUpdated' => $segment->last_applied_at?->diffForHumans()
    ]);
  }

  public function update(Request $request, Segment $segment)
  {
    $this->authorize('update', $segment);

    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'description' => ['nullable', 'string'],
      'conditions' => ['required', 'array'],
      'conditions.*.type' => ['required', Rule::in(['field', 'activity', 'campaign', 'list'])],
      'match_type' => ['required', Rule::in(['all', 'any'])],
      'metadata' => ['nullable', 'array']
    ]);

    $segment->update($validated);

    // Apply updated conditions
    $segment->updateRecipients();

    return back()->with('success', 'Segment updated successfully.');
  }

  public function destroy(Segment $segment)
  {
    $this->authorize('delete', $segment);

    $segment->delete();

    return redirect()->route('segments.index')
      ->with('success', 'Segment deleted successfully.');
  }

  public function refresh(Segment $segment)
  {
    $this->authorize('update', $segment);

    $segment->updateRecipients();

    return back()->with('success', 'Segment recipients updated successfully.');
  }
}
