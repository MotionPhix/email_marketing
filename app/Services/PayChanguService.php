<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class PayChanguService
{
  protected $baseUrl;
  protected $apiKey;
  protected $secretKey;

  public function __construct()
  {
    $this->baseUrl = config('services.paychangu.url');
    $this->apiKey = config('services.paychangu.key');
    $this->secretKey = config('services.paychangu.secret');
  }

  public function initiatePayment(User $user, Plan $plan, $returnUrl)
  {
    $response = Http::withHeaders([
      'Authorization' => 'Bearer ' . $this->apiKey,
    ])->post($this->baseUrl . '/payments', [
      'amount' => $plan->price,
      'currency' => 'MWK',
      'email' => $user->email,
      'first_name' => $user->first_name,
      'last_name' => $user->last_name,
      'reference' => 'SUB_' . uniqid(),
      'return_url' => $returnUrl,
      'callback_url' => route('webhooks.paychangu'),
      'description' => "Subscription to {$plan->name} plan",
    ]);

    if ($response->successful()) {
      return $response->json();
    }

    throw new \Exception('Payment initiation failed: ' . $response->body());
  }

  public function verifyPayment($reference)
  {
    $response = Http::withHeaders([
      'Authorization' => 'Bearer ' . $this->apiKey,
    ])->get($this->baseUrl . '/payments/' . $reference);

    if ($response->successful()) {
      return $response->json();
    }

    throw new \Exception('Payment verification failed: ' . $response->body());
  }
}
