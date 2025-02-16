<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportContacts implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public function __construct(
    protected User $user,
    protected array $contacts
  ) {}

  public function handle(): void
  {
    $batchSize = 100;
    $chunks = array_chunk($this->contacts, $batchSize);

    foreach ($chunks as $chunk) {
      $records = collect($chunk)->map(function ($contact) {
        return [
          'user_id' => $this->user->id,
          'email' => $contact['email'],
          'first_name' => $contact['first_name'] ?? null,
          'last_name' => $contact['last_name'] ?? null,
          'status' => 'subscribed',
          'created_at' => now(),
          'updated_at' => now(),
        ];
      });

      // Batch insert subscribers
      $this->user->subscribers()->insert($records->toArray());
    }

    // Update user's onboarding progress data with import stats
    $progress = $this->user->onboardingProgress;
    $formData = $progress->form_data;
    $formData['step_2']['imported_count'] = count($this->contacts);
    $progress->update(['form_data' => $formData]);
  }
}
