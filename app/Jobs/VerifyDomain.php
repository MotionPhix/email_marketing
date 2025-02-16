<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class VerifyDomain implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public int $tries = 3;
  public int $maxExceptions = 1;

  public function __construct(
    protected User $user,
    protected string $domain
  ) {}

  public function handle(): void
  {
    // Generate unique verification token
    $verificationToken = Str::random(32);

    // Create domain authentication via SendGrid API
    $response = Http::withToken(config('services.sendgrid.key'))
      ->post('https://api.sendgrid.com/v3/whitelabel/domains', [
        'domain' => $this->domain,
        'subdomain' => 'mail',
        'default' => true,
        'automatic_security' => true,
        'custom_spf' => false,
        'custom_dkim' => false,
      ]);

    if (!$response->successful()) {
      throw new \Exception('Failed to initiate domain verification with SendGrid: ' . $response->body());
    }

    $domainId = $response->json('id');

    // Get DNS records that need to be added
    $dnsResponse = Http::withToken(config('services.sendgrid.key'))
      ->get("https://api.sendgrid.com/v3/whitelabel/domains/{$domainId}");

    if (!$dnsResponse->successful()) {
      throw new \Exception('Failed to fetch DNS records from SendGrid');
    }

    $dnsRecords = $dnsResponse->json('dns_records');

    // Update user's onboarding progress with DNS records
    $progress = $this->user->onboardingProgress;
    $formData = $progress->form_data;
    $formData['step_3'] = array_merge($formData['step_3'] ?? [], [
      'domain' => $this->domain,
      'domain_id' => $domainId,
      'dns_records' => $dnsRecords,
      'verification_token' => $verificationToken,
      'verification_status' => 'pending'
    ]);

    $progress->update(['form_data' => $formData]);

    // Schedule a verification check
    VerifyDomainStatus::dispatch($this->user, $domainId)
      ->delay(now()->addMinutes(5));
  }
}
