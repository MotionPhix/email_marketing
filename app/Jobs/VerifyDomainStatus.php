<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class VerifyDomainStatus implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public int $tries = 12; // Will try for 1 hour (5 min intervals)
  public int $backoff = 300; // 5 minutes between attempts

  public function __construct(
    protected User $user,
    protected int $domainId
  ) {}

  public function handle(): void
  {
    $response = Http::withToken(config('services.sendgrid.key'))
      ->get("https://api.sendgrid.com/v3/whitelabel/domains/{$this->domainId}/validate");

    if (!$response->successful()) {
      if ($this->attempts() === $this->tries) {
        $this->markAsFailed();
      }
      $this->release($this->backoff);
      return;
    }

    $isValid = $response->json('valid');

    // Update progress with verification status
    $progress = $this->user->onboardingProgress;
    $formData = $progress->form_data;
    $formData['step_3']['verification_status'] = $isValid ? 'verified' : 'failed';

    if ($isValid) {
      // Update user's sending domain
      $this->user->update([
        'sending_domain' => $formData['step_3']['domain'],
        'domain_verified_at' => now()
      ]);
    }

    $progress->update(['form_data' => $formData]);
  }

  private function markAsFailed(): void
  {
    $progress = $this->user->onboardingProgress;
    $formData = $progress->form_data;
    $formData['step_3']['verification_status'] = 'failed';
    $progress->update(['form_data' => $formData]);
  }
}
