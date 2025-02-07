<?php

namespace App\Modules\Billing\Services;

use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayChanguService
{
  protected string $baseUrl;
  protected string $apiKey;
  protected string $merchantId;

  public function __construct()
  {
    $this->baseUrl = config('billing.paychangu.base_url', 'https://api.paychangu.com/v1');
    $this->apiKey = config('billing.paychangu.api_key');
    $this->merchantId = config('billing.paychangu.merchant_id');
  }

  public function createPaymentRequest(Plan $plan, string $callbackUrl): array
  {
    try {
      $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $this->apiKey,
        'Accept' => 'application/json',
      ])->post("{$this->baseUrl}/payment-request", [
        'merchant_id' => $this->merchantId,
        'amount' => $plan->price,
        'currency' => $plan->currency,
        'reference' => "plan_{$plan->uuid}_" . time(),
        'description' => "Subscription to {$plan->name} plan",
        'callback_url' => $callbackUrl,
        'cancel_url' => route('billing.cancelled'),
        'success_url' => route('billing.success'),
      ]);

      if ($response->successful()) {
        return $response->json();
      }

      Log::error('PayChangu payment request failed', [
        'status' => $response->status(),
        'response' => $response->json(),
      ]);

      throw new \Exception('Failed to create payment request');
    } catch (\Exception $e) {
      Log::error('PayChangu service error', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      throw $e;
    }
  }

  public function verifyPayment(string $transactionId): array
  {
    try {
      $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $this->apiKey,
        'Accept' => 'application/json',
      ])->get("{$this->baseUrl}/payment-status/{$transactionId}");

      if ($response->successful()) {
        return $response->json();
      }

      Log::error('PayChangu payment verification failed', [
        'status' => $response->status(),
        'response' => $response->json(),
      ]);

      throw new \Exception('Failed to verify payment');
    } catch (\Exception $e) {
      Log::error('PayChangu verification error', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      throw $e;
    }
  }

  public function handleWebhook(array $payload): void
  {
    // Verify webhook signature
    if (!$this->verifyWebhookSignature($payload)) {
      Log::error('Invalid webhook signature');
      throw new \Exception('Invalid webhook signature');
    }

    // Extract transaction ID from the reference
    preg_match('/plan_(.+)_\d+/', $payload['reference'], $matches);
    $planUuid = $matches[1] ?? null;

    if (!$planUuid) {
      Log::error('Invalid plan reference in webhook', $payload);
      throw new \Exception('Invalid plan reference');
    }

    // Find subscription by transaction ID
    $subscription = Subscription::where('paychangu_transaction_id', $payload['transaction_id'])->first();

    if (!$subscription) {
      Log::error('Subscription not found for transaction', $payload);
      throw new \Exception('Subscription not found');
    }

    // Update subscription status based on payment status
    $subscription->update([
      'paychangu_payment_status' => $payload['status'],
      'status' => $this->mapPaymentStatus($payload['status']),
      'last_payment_at' => now(),
      'payment_metadata' => $payload,
    ]);
  }

  protected function verifyWebhookSignature(array $payload): bool
  {
    $signature = request()->header('X-PayChangu-Signature');
    $expectedSignature = hash_hmac('sha256', json_encode($payload), $this->apiKey);

    return hash_equals($expectedSignature, $signature);
  }

  protected function mapPaymentStatus(string $paychanguStatus): string
  {
    return match ($paychanguStatus) {
      'completed' => Subscription::STATUS_ACTIVE,
      'failed' => Subscription::STATUS_EXPIRED,
      'pending' => Subscription::STATUS_SCHEDULED,
      default => Subscription::STATUS_EXPIRED,
    };
  }
}
