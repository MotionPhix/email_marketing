<?php

namespace App\Imports;

use App\Models\Subscriber;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SubscribersImport implements ToCollection, WithHeadingRow
{
  protected $team;
  protected $results;

  public function __construct($team, &$results)
  {
    $this->team = $team;
    $this->results = &$results;
  }

  public function collection(Collection $rows)
  {
    $this->results['total'] = count($rows);

    foreach ($rows as $row) {
      try {
        $subscriberData = [
          'email' => $row['email'] ?? null,
          'first_name' => $row['first_name'] ?? null,
          'last_name' => $row['last_name'] ?? null,
          'company' => $row['company'] ?? null,
          'status' => $row['status'] ?? Subscriber::STATUS_SUBSCRIBED,
        ];

        // Validate the data
        $validator = Validator::make($subscriberData, [
          'email' => ['required', 'email', Rule::unique('subscribers', 'email')
            ->where('team_id', $this->team->id)
            ->ignore(optional(Subscriber::where('email', $subscriberData['email'])
              ->where('team_id', $this->team->id)
              ->first())->id)],
          'first_name' => 'required|string|max:255',
          'last_name' => 'required|string|max:255',
          'company' => 'nullable|string|max:255',
          'status' => [
            'nullable',
            Rule::in([
              Subscriber::STATUS_SUBSCRIBED,
              Subscriber::STATUS_UNSUBSCRIBED,
              Subscriber::STATUS_BOUNCED,
              Subscriber::STATUS_COMPLAINED
            ])
          ]
        ]);

        if ($validator->fails()) {
          $this->results['failed']++;
          $this->results['errors'][] = [
            'row' => $this->results['total'] - count($rows) + $row->getIndex() + 2,
            'data' => $subscriberData,
            'errors' => $validator->errors()->toArray()
          ];
          continue;
        }

        // Set default status if not provided
        $subscriberData['status'] ??= Subscriber::STATUS_SUBSCRIBED;

        // Try to find existing subscriber
        $subscriber = Subscriber::where('email', $subscriberData['email'])
          ->where('team_id', $this->team->id)
          ->first();

        if ($subscriber) {
          $subscriber->update($subscriberData);
          $this->results['updated']++;
        } else {
          $this->team->subscribers()->create(array_merge($subscriberData, [
            'user_id' => auth()->id()
          ]));
          $this->results['imported']++;
        }

      } catch (\Exception $e) {
        $this->results['failed']++;
        $this->results['errors'][] = [
          'row' => $this->results['total'] - count($rows) + $row->getIndex() + 2,
          'data' => $row->toArray(),
          'errors' => ['system' => [$e->getMessage()]]
        ];
      }
    }
  }

  public function getResults()
  {
    return $this->results;
  }
}
