<?php

namespace App\Http\Controllers;

use App\Imports\SubscribersImport;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use App\Http\Requests\StoreSubscriberRequest;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class SubscriberController extends Controller
{
  public function index(Request $request)
  {
    $query = Subscriber::query()
      ->where('team_id', $request->user()->currentTeam->id)
      ->with(['campaignEvents' => function ($query) {
        $query->select('id', 'subscriber_id', 'type', 'created_at');
      }]);

    // Search
    if ($request->search) {
      $query->where(function ($q) use ($request) {
        $q->where('email', 'like', "%{$request->search}%")
          ->orWhere('first_name', 'like', "%{$request->search}%")
          ->orWhere('last_name', 'like', "%{$request->search}%")
          ->orWhere('company', 'like', "%{$request->search}%");
      });
    }

    // Filter by status
    if ($request->status && $request->status !== 'all') {
      $query->where('status', $request->status);
    }

    // Sort
    $sortField = $request->sort ?? 'created_at';
    $sortDirection = $request->direction ?? 'desc';
    $query->orderBy($sortField, $sortDirection);

    $subscribers = $query->paginate(10)
      ->withQueryString()
      ->through(fn ($subscriber) => [
        'id' => $subscriber->id,
        'email' => $subscriber->email,
        'first_name' => $subscriber->first_name,
        'last_name' => $subscriber->last_name,
        'company' => $subscriber->company,
        'status' => $subscriber->status,
        'metadata' => $subscriber->metadata,
        'unsubscribed_at' => $subscriber->unsubscribed_at,
        'created_at' => $subscriber->created_at,
        'campaign_stats' => [
          'total_received' => $subscriber->campaignEvents->where('type', 'sent')->count(),
          'total_opened' => $subscriber->campaignEvents->where('type', 'opened')->count(),
          'total_clicked' => $subscriber->campaignEvents->where('type', 'clicked')->count(),
        ]
      ]);

    $stats = [
      'total' => Subscriber::where('team_id', $request->user()->currentTeam->id)->count(),
      'subscribed' => Subscriber::where('team_id', $request->user()->currentTeam->id)
        ->where('status', Subscriber::STATUS_SUBSCRIBED)->count(),
      'unsubscribed' => Subscriber::where('team_id', $request->user()->currentTeam->id)
        ->where('status', Subscriber::STATUS_UNSUBSCRIBED)->count(),
      'bounced' => Subscriber::where('team_id', $request->user()->currentTeam->id)
        ->where('status', Subscriber::STATUS_BOUNCED)->count(),
      'complained' => Subscriber::where('team_id', $request->user()->currentTeam->id)
        ->where('status', Subscriber::STATUS_COMPLAINED)->count(),
    ];

    return Inertia::render('Subscribers/Index', [
      'subscribers' => $subscribers,
      'filters' => $request->only(['search', 'status', 'sort', 'direction']),
      'stats' => $stats
    ]);
  }

  public function store(StoreSubscriberRequest $request)
  {
    $subscriber = $request->user()->currentTeam->subscribers()->create($request->validated());

    return back()->with('success', 'Subscriber added successfully.');
  }

  public function update(Request $request, Subscriber $subscriber)
  {
    $validated = $request->validate([
      'email' => ['required', 'email', Rule::unique('subscribers')->ignore($subscriber->id)],
      'first_name' => 'required|string|max:255',
      'last_name' => 'required|string|max:255',
      'company' => 'nullable|string|max:255',
      'status' => ['required', Rule::in([
        Subscriber::STATUS_SUBSCRIBED,
        Subscriber::STATUS_UNSUBSCRIBED,
        Subscriber::STATUS_BOUNCED,
        Subscriber::STATUS_COMPLAINED
      ])],
      'metadata' => 'nullable|array'
    ]);

    $subscriber->update($validated);

    return back()->with('success', 'Subscriber updated successfully.');
  }

  public function destroy(Subscriber $subscriber)
  {
    $subscriber->delete();

    return back()->with('success', 'Subscriber removed successfully.');
  }

  public function bulkDestroy(Request $request)
  {
    $validated = $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:subscribers,id'
    ]);

    Subscriber::whereIn('id', $validated['ids'])->delete();

    return back()->with('success', 'Selected subscribers removed successfully.');
  }

  public function bulkUpdate(Request $request)
  {
    $validated = $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'exists:subscribers,id',
      'status' => ['required', Rule::in([
        Subscriber::STATUS_SUBSCRIBED,
        Subscriber::STATUS_UNSUBSCRIBED,
        Subscriber::STATUS_BOUNCED,
        Subscriber::STATUS_COMPLAINED
      ])]
    ]);

    Subscriber::whereIn('id', $validated['ids'])
      ->update(['status' => $validated['status']]);

    return back()->with('success', 'Selected subscribers updated successfully.');
  }

  public function import(Request $request)
  {
    $request->validate([
      'file' => [
        'required',
        'file',
        'max:10240', // 10MB max size
        'mimes:csv,txt,xlsx,xls' // Allow Excel and CSV files
      ]
    ]);

    try {
      // Start database transaction
      DB::beginTransaction();

      $file = $request->file('file');
      $extension = $file->getClientOriginalExtension();
      $rows = [];

      if (in_array($extension, ['xlsx', 'xls'])) {
        // Handle Excel files
        $rows = Excel::toCollection(new SubscribersImport, $file)->first();
      } else {
        // Handle CSV files
        $rows = $this->parseCsvFile($file);
      }

      // Remove header row if present
      if ($rows->count() > 0 && $this->isHeaderRow($rows->first())) {
        $rows = $rows->slice(1);
      }

      $team_id = $request->user()->currentTeam->id;
      $importedCount = 0;
      $updatedCount = 0;
      $errorCount = 0;
      $errors = [];

      foreach ($rows as $index => $row) {
        try {
          // Map row data to subscriber fields
          $subscriberData = $this->mapRowToSubscriberData($row);

          // Validate each row
          $validator = Validator::make($subscriberData, [
            'email' => ['required', 'email', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([
              Subscriber::STATUS_SUBSCRIBED,
              Subscriber::STATUS_UNSUBSCRIBED,
              Subscriber::STATUS_BOUNCED,
              Subscriber::STATUS_COMPLAINED
            ])]
          ]);

          if ($validator->fails()) {
            $errors[] = [
              'row' => $index + 2, // Account for 1-based index and header row
              'errors' => $validator->errors()->toArray(),
              'data' => $subscriberData
            ];
            $errorCount++;
            continue;
          }

          // Check for existing subscriber
          $subscriber = Subscriber::where('email', $subscriberData['email'])
            ->where('team_id', $team_id)
            ->first();

          if ($subscriber) {
            // Update existing subscriber
            $subscriber->update($subscriberData);
            $updatedCount++;
          } else {
            // Create new subscriber
            $subscriberData['team_id'] = $team_id;
            Subscriber::create($subscriberData);
            $importedCount++;
          }

        } catch (\Exception $e) {
          $errors[] = [
            'row' => $index + 2,
            'errors' => ['system' => $e->getMessage()],
            'data' => $subscriberData ?? $row
          ];
          $errorCount++;
        }
      }

      DB::commit();

      // Prepare response message
      $message = "Import completed. ";
      $message .= $importedCount > 0 ? "{$importedCount} subscribers imported. " : "";
      $message .= $updatedCount > 0 ? "{$updatedCount} subscribers updated. " : "";

      if ($errorCount > 0) {
        // Store errors in session for detailed view
        session()->flash('import_errors', $errors);
        return back()->with([
          'warning' => $message . "{$errorCount} rows had errors. View details below.",
          'import_summary' => [
            'imported' => $importedCount,
            'updated' => $updatedCount,
            'errors' => $errorCount
          ]
        ]);
      }

      return back()->with('success', $message);

    } catch (\Exception $e) {
      DB::rollBack();
      report($e);
      return back()->with('error', 'Import failed. Please check your file and try again.');
    }
  }

  private function parseCsvFile($file)
  {
    $rows = new Collection();
    $handle = fopen($file->getPathname(), 'r');

    while (($data = fgetcsv($handle)) !== false) {
      $rows->push($data);
    }

    fclose($handle);
    return $rows;
  }

  private function isHeaderRow($row)
  {
    // Check if the first row contains headers
    $possibleHeaders = ['email', 'first_name', 'last_name', 'company', 'status'];
    $rowValues = collect($row)->map(fn($value) => strtolower(trim($value)));

    return $rowValues->intersect($possibleHeaders)->isNotEmpty();
  }

  private function mapRowToSubscriberData($row)
  {
    // Convert array or object to array
    $data = is_array($row) ? $row : $row->toArray();

    // If it's a CSV row, map by position
    if (isset($data[0])) {
      return [
        'email' => $data[0] ?? null,
        'first_name' => $data[1] ?? null,
        'last_name' => $data[2] ?? null,
        'company' => $data[3] ?? null,
        'status' => $data[4] ?? Subscriber::STATUS_SUBSCRIBED,
      ];
    }

    // If it's an Excel row with headers, map by key
    return [
      'email' => $data['email'] ?? null,
      'first_name' => $data['first_name'] ?? null,
      'last_name' => $data['last_name'] ?? null,
      'company' => $data['company'] ?? null,
      'status' => $data['status'] ?? Subscriber::STATUS_SUBSCRIBED,
    ];
  }

  public function export(Request $request)
  {
    $fileName = 'subscribers-' . now()->format('Y-m-d') . '.csv';

    return response()->streamDownload(function() use ($request) {
      $subscribers = Subscriber::where('team_id', $request->user()->currentTeam->id)
        ->get(['email', 'first_name', 'last_name', 'company', 'status', 'created_at']);

      $file = fopen('php://output', 'w');
      fputcsv($file, ['Email', 'First Name', 'Last Name', 'Company', 'Status', 'Joined Date']);

      foreach ($subscribers as $subscriber) {
        fputcsv($file, [
          $subscriber->email,
          $subscriber->first_name,
          $subscriber->last_name,
          $subscriber->company,
          $subscriber->status,
          $subscriber->created_at->format('Y-m-d H:i:s')
        ]);
      }

      fclose($file);
    }, $fileName);
  }
}
