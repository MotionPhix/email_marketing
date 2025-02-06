<?php

namespace App\Services\Subscription;

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionRenewalFailed;
use App\Notifications\SubscriptionRenewalSuccess;
use App\Notifications\SubscriptionExpiringNotification;
use App\Services\PayChanguService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SubscriptionRenewalService
{
  protected $paychanguService;

  public function __construct(PayChanguService $paychanguService)
  {
    $this->paychanguService = $paychanguService;
  }

  public function processRenewals()
  {
    $this->notifyExpiringSubscriptions();
    $this->processAutoRenewals();
    $this->deactivateExpiredSubscriptions();
  }

  protected function notifyExpiringSubscriptions()
  {
    // Get subscriptions expiring in 3 days
    $expiringSubscriptions = Subscription::query()
      ->where('status', Subscription::STATUS_ACTIVE)
      ->where('ends_at', '>', now())
      ->where('ends_at', '<=', now()->addDays(3))
      ->whereNull('renewal_notified_at')
      ->get();

    foreach ($expiringSubscriptions as $subscription) {
      try {
        $subscription->user->notify(new SubscriptionExpiringNotification($subscription));
        $subscription->update(['renewal_notified_at' => now()]);
      } catch (\Exception $e) {
        Log::error('Failed to send expiration notification', [
          'subscription_id' => $subscription->id,
          'error' => $e->getMessage()
        ]);
      }
    }
  }

  protected function processAutoRenewals()
  {
    // Get subscriptions due for renewal (expiring in 24 hours)
    $dueForRenewal = Subscription::query()
      ->where('status', Subscription::STATUS_ACTIVE)
      ->where('auto_renew', true)
      ->where('ends_at', '<=', now()->addDay())
      ->where('ends_at', '>', now())
      ->get();

    foreach ($dueForRenewal as $subscription) {
      try {
        $this->renewSubscription($subscription);
      } catch (\Exception $e) {
        Log::error('Subscription renewal failed', [
          'subscription_id' => $subscription->id,
          'error' => $e->getMessage()
        ]);

        $subscription->user->notify(new SubscriptionRenewalFailed($subscription));
      }
    }
  }

  protected function renewSubscription(Subscription $subscription)
  {
    // Attempt payment through PayChangu
    $payment = $this->paychanguService->initiatePayment(
      $subscription->user,
      $subscription->plan,
      route('subscription.renewal.callback', $subscription->uuid)
    );

    // Create renewal record
    $subscription->renewals()->create([
      'paychangu_reference' => $payment['reference'],
      'amount' => $subscription->plan->price,
      'status' => 'pending'
    ]);

    return $payment;
  }

  protected function deactivateExpiredSubscriptions()
  {
    Subscription::query()
      ->where('status', Subscription::STATUS_ACTIVE)
      ->where('ends_at', '<', now())
      ->update([
        'status' => Subscription::STATUS_EXPIRED
      ]);
  }

  public function handleRenewalCallback(string $subscriptionUuid, array $paymentData)
  {
    $subscription = Subscription::where('uuid', $subscriptionUuid)->firstOrFail();
    $renewal = $subscription->renewals()->latest()->firstOrFail();

    if ($paymentData['status'] === 'success') {
      $this->processSuccessfulRenewal($subscription, $renewal);
    } else {
      $this->processFailedRenewal($subscription, $renewal);
    }
  }

  protected function processSuccessfulRenewal(Subscription $subscription, $renewal)
  {
    $now = now();

    $renewal->update([
      'status' => 'completed',
      'completed_at' => $now
    ]);

    $subscription->update([
      'ends_at' => $subscription->ends_at->addMonth(),
      'renewal_notified_at' => null,
      'last_payment_at' => $now
    ]);

    $subscription->user->notify(new SubscriptionRenewalSuccess($subscription));
  }

  protected function processFailedRenewal(Subscription $subscription, $renewal)
  {
    $renewal->update([
      'status' => 'failed',
      'failed_at' => now()
    ]);

    $subscription->user->notify(new SubscriptionRenewalFailed($subscription));
  }
}
