<?php

namespace App\Modules\Billing\Services;

use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingService
{
  public function __construct(
    protected PayChanguService $paychangu,
    protected SubscriptionService $subscriptions
  ) {}

  /**
   * Initialize a subscription and create a PayChangu payment request
   */
  public function initiateSubscription(array $data): array
  {
    return DB::transaction(function () use ($data) {
      // Create pending subscription
      $subscription = $this->subscriptions->create([
        'user_id' => $data['user_id'],
        'plan_id' => $data['plan_id'],
        'status' => Subscription::STATUS_SCHEDULED,
        'starts_at' => now(),
        'trial_ends_at' => $this->calculateTrialEnd($data['plan_id']),
      ]);

      try {
        // Create PayChangu payment request
        $paymentRequest = $this->paychangu->createPaymentRequest(
          plan: Plan::findOrFail($data['plan_id']),
          callbackUrl: route('webhooks.paychangu')
        );

        // Update subscription with PayChangu transaction ID
        $subscription->update([
          'paychangu_transaction_id' => $paymentRequest['transaction_id'],
          'payment_metadata' => [
            'payment_request_id' => $paymentRequest['id'],
            'created_at' => now()->toIso8601String(),
          ],
        ]);

        return [
          'subscription' => $subscription,
          'payment_url' => $paymentRequest['payment_url'],
        ];
      } catch (\Exception $e) {
        Log::error('Failed to create PayChangu payment request', [
          'error' => $e->getMessage(),
          'subscription_id' => $subscription->id,
        ]);

        throw $e;
      }
    });
  }

  /**
   * Cancel an active subscription
   */
  public function cancelSubscription(Subscription $subscription): bool
  {
    if (!$subscription->isActive()) {
      return false;
    }

    return DB::transaction(function () use ($subscription) {
      return $this->subscriptions->cancel($subscription);
    });
  }

  /**
   * Change subscription plan
   */
  public function changePlan(Subscription $currentSubscription, Plan $newPlan): array
  {
    return DB::transaction(function () use ($currentSubscription, $newPlan) {
      // If upgrading to a more expensive plan
      if ($newPlan->price > $currentSubscription->plan->price) {
        // Calculate prorated amount
        $remainingDays = now()->diffInDays($currentSubscription->ends_at);
        $totalDays = $currentSubscription->starts_at->diffInDays($currentSubscription->ends_at);
        $unusedAmount = ($currentSubscription->plan->price / $totalDays) * $remainingDays;

        // Create new payment request for the difference
        $amountToPay = $newPlan->price - $unusedAmount;

        try {
          $paymentRequest = $this->paychangu->createPaymentRequest(
            plan: $newPlan,
            callbackUrl: route('webhooks.paychangu')
          );

          // Create new subscription with pending status
          $newSubscription = $this->subscriptions->create([
            'user_id' => $currentSubscription->user_id,
            'plan_id' => $newPlan->id,
            'status' => Subscription::STATUS_PENDING,
            'starts_at' => now(),
            'paychangu_transaction_id' => $paymentRequest['transaction_id'],
            'payment_metadata' => [
              'payment_request_id' => $paymentRequest['id'],
              'prorated_amount' => $amountToPay,
              'previous_subscription_id' => $currentSubscription->id,
              'change_type' => 'upgrade',
              'created_at' => now()->toIso8601String(),
            ],
          ]);

          // Cancel current subscription
          $this->subscriptions->cancel($currentSubscription);

          return [
            'subscription' => $newSubscription,
            'payment_url' => $paymentRequest['payment_url'],
          ];
        } catch (\Exception $e) {
          Log::error('Failed to create upgrade payment request', [
            'error' => $e->getMessage(),
            'current_subscription_id' => $currentSubscription->id,
            'new_plan_id' => $newPlan->id,
          ]);

          throw $e;
        }
      } else {
        // If downgrading to a less expensive plan
        // Schedule the downgrade for the end of the current period
        $scheduledSubscription = $this->subscriptions->scheduleChange(
          currentSubscription: $currentSubscription,
          newPlan: $newPlan
        );

        return [
          'subscription' => $scheduledSubscription,
          'payment_url' => null, // No payment needed for downgrade
        ];
      }
    });
  }

  /**
   * Check subscription status with PayChangu
   */
  public function checkSubscriptionStatus(Subscription $subscription): array
  {
    if (!$subscription->paychangu_transaction_id) {
      throw new \Exception('No PayChangu transaction ID found for subscription');
    }

    try {
      $paymentStatus = $this->paychangu->verifyPayment(
        $subscription->paychangu_transaction_id
      );

      $subscription->update([
        'paychangu_payment_status' => $paymentStatus['status'],
        'payment_metadata' => array_merge(
          $subscription->payment_metadata ?? [],
          ['last_verified_at' => now()->toIso8601String()]
        ),
      ]);

      return $paymentStatus;
    } catch (\Exception $e) {
      Log::error('Failed to verify PayChangu payment status', [
        'error' => $e->getMessage(),
        'subscription_id' => $subscription->id,
      ]);

      throw $e;
    }
  }

  /**
   * Calculate trial end date based on plan settings
   */
  protected function calculateTrialEnd(int $planId): ?\DateTime
  {
    $plan = Plan::find($planId);

    if (!$plan || $plan->trial_days === 0) {
      return null;
    }

    return now()->addDays($plan->trial_days);
  }
}
