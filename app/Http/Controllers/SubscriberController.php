<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriberController extends Controller
{
  public function index()
  {
    $subscribers = Subscriber::query()
      ->latest()
      ->paginate(10);

    return Inertia::render('Subscribers/Index', [
      'subscribers' => $subscribers
    ]);
  }

  public function create()
  {
    return Inertia::render('Subscribers/Form', [
      'subscriber' => null
    ]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'email' => 'required|email|unique:subscribers,email',
      'first_name' => 'nullable|string|max:255',
      'last_name' => 'nullable|string|max:255',
      'company' => 'nullable|string|max:255',
      'metadata' => 'nullable|array',
    ]);

    Subscriber::create($validated);

    return redirect()->route('subscribers.index')
      ->with('success', 'Subscriber added successfully.');
  }

  public function edit(Subscriber $subscriber)
  {
    return Inertia::render('Subscribers/Form', [
      'subscriber' => $subscriber
    ]);
  }

  public function update(Request $request, Subscriber $subscriber)
  {
    $validated = $request->validate([
      'email' => 'required|email|unique:subscribers,email,' . $subscriber->id,
      'first_name' => 'nullable|string|max:255',
      'last_name' => 'nullable|string|max:255',
      'company' => 'nullable|string|max:255',
      'metadata' => 'nullable|array',
    ]);

    $subscriber->update($validated);

    return redirect()->route('subscribers.index')
      ->with('success', 'Subscriber updated successfully.');
  }

  public function destroy(Subscriber $subscriber)
  {
    $subscriber->delete();

    return redirect()->route('subscribers.index')
      ->with('success', 'Subscriber deleted successfully.');
  }

  public function import(Request $request)
  {
    $request->validate([
      'file' => 'required|file|mimes:csv,txt|max:10240'
    ]);

    // Handle CSV import logic here
    // You might want to queue this for large files

    return redirect()->route('subscribers.index')
      ->with('success', 'Import started successfully.');
  }

  public function export()
  {
    return response()->streamDownload(function () {
      $handle = fopen('php://output', 'w');

      // Headers
      fputcsv($handle, ['Email', 'First Name', 'Last Name', 'Company', 'Status', 'Subscribed Date']);

      // Data
      Subscriber::chunk(1000, function ($subscribers) use ($handle) {
        foreach ($subscribers as $subscriber) {
          fputcsv($handle, [
            $subscriber->email,
            $subscriber->first_name,
            $subscriber->last_name,
            $subscriber->company,
            $subscriber->status,
            $subscriber->created_at->format('Y-m-d H:i:s'),
          ]);
        }
      });

      fclose($handle);
    }, 'subscribers.csv');
  }
}
