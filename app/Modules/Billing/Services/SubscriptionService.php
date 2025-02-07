<?php

namespace App\Modules\Billing\Services;

use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
  public function create(array $data): Subscription
  {
    return Subscription::create([
      'user_id' => $data['user_id'],
      'plan_id' => $data['plan_id'],
      'status' => $data['status'] ?? Subscription::STATUS_PENDING,
      'starts_at' => $data['starts_at'] ?? now(),
      'ends_at' => $data['ends_at'] ?? Carbon::now()->addMonth(),
      'trial_ends_at' => $data['trial_ends_at'] ?? null,
      'paychangu_transaction_id' => $data['paychangu_transaction_id'] ?? null,
      'payment_metadata' => $data['payment_metadata'] ?? null,
    ]);
  }

  public function cancel(Subscription $subscription): bool
  {
    try {
      return $subscription->update([
        'status' => Subscription::STATUS_CANCELLED,
        'cancelled_at' => now(),
        'payment_metadata' => array_merge(
          $subscription->payment_metadata ?? [],
          ['cancelled_at' => now()->toIso8601String()]
        )
      ]);
    } catch (\Exception $e) {
      Log::error('Failed to cancel subscription', [
        'subscription_id' => $subscription->id,
        'error' => $e->getMessage()
      ]);

      throw $e;
    }
  }

  public function scheduleChange(Subscription $currentSubscription, Plan $newPlan): Subscription
  {
    try {
      // Create new subscription that starts when current one ends
      return $this->create([
        'user_id' => $currentSubscription->user_id,
        'plan_id' => $newPlan->id,
        'status' => Subscription::STATUS_SCHEDULED,
        'starts_at' => $currentSubscription->ends_at,
        'ends_at' => $currentSubscription->ends_at->copy()->addMonth(),
        'payment_metadata' => [
          'previous_subscription_id' => $currentSubscription->id,
          'scheduled_at' => now()->toIso8601String(),
          'change_type' => 'downgrade',
        ]
      ]);
    } catch (\Exception $e) {
      Log::error('Failed to schedule plan change', [
        'current_subscription_id' => $currentSubscription->id,
        'new_plan_id' => $newPlan->id,
        'error' => $e->getMessage()
      ]);

      throw $e;
    }
  }

  public function activateScheduledSubscription(Subscription $scheduledSubscription): bool
  {
    try {
      // Get the previous subscription from metadata
      $previousSubscriptionId = $scheduledSubscription->payment_metadata['previous_subscription_id'] ?? null;

      if ($previousSubscriptionId) {
        $previousSubscription = Subscription::find($previousSubscriptionId);
        if ($previousSubscription) {
          $this->cancel($previousSubscription);
        }
      }

      return $scheduledSubscription->update([
        'status' => Subscription::STATUS_ACTIVE,
        'payment_metadata' => array_merge(
          $scheduledSubscription->payment_metadata ?? [],
          ['activated_at' => now()->toIso8601String()]
        )
      ]);
    } catch (\Exception $e) {
      Log::error('Failed to activate scheduled subscription', [
        'subscription_id' => $scheduledSubscription->id,
        'error' => $e->getMessage()
      ]);

      throw $e;
    }
  }

  public function updatePaymentStatus(
    Subscription $subscription,
    string $paymentStatus,
    array $metadata = []
  ): bool {
    try {
      return $subscription->update([
        'paychangu_payment_status' => $paymentStatus,
        'status' => $this->mapPaymentStatus($paymentStatus),
        'last_payment_at' => now(),
        'payment_metadata' => array_merge(
          $subscription->payment_metadata ?? [],
          $metadata,
          ['payment_updated_at' => now()->toIso8601String()]
        )
      ]);
    } catch (\Exception $e) {
      Log::error('Failed to update payment status', [
        'subscription_id' => $subscription->id,
        'payment_status' => $paymentStatus,
        'error' => $e->getMessage()
      ]);

      throw $e;
    }
  }

  protected function mapPaymentStatus(string $paymentStatus): string
  {
    return match ($paymentStatus) {
      'completed' => Subscription::STATUS_ACTIVE,
      'failed' => Subscription::STATUS_EXPIRED,
      'pending' => Subscription::STATUS_PENDING,
      default => Subscription::STATUS_PENDING,
    };
  }
}
