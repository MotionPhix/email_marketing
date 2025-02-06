<?php

namespace App\Services\Subscription;

use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionRenewal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
  public function subscribe(User $user, Plan $plan, string $billingPeriod = 'monthly'): Subscription
  {
    return DB::transaction(function () use ($user, $plan, $billingPeriod) {
      $currentSubscription = $user->activeSubscription();
      $pendingSubscription = $user->subscription()
        ->where('status', Subscription::STATUS_PENDING)
        ->first();

      // If there's a pending subscription, we can't create a new one
      if ($pendingSubscription) {
        throw new \Exception('You already have a pending subscription change.');
      }

      // If user has an active subscription
      if ($currentSubscription) {
        // If switching to free plan, cancel current subscription
        if ($plan->price === 0) {
          return $this->cancel($user);
        }

        // Determine if this is an upgrade or downgrade
        $isUpgrade = $plan->price > $currentSubscription->plan->price;

        // For downgrades, take effect after current subscription ends
        if (!$isUpgrade) {
          $startDate = $currentSubscription->ends_at;
          return $this->createPendingSubscription($user, $plan, $startDate, $billingPeriod);
        }

        // For upgrades, cancel current subscription
        $currentSubscription->update([
          'status' => Subscription::STATUS_CANCELLED,
          'ends_at' => now()
        ]);
      }

      // Create new subscription in pending state
      return $this->createPendingSubscription($user, $plan, now(), $billingPeriod);
    });
  }

  private function createPendingSubscription(User $user, Plan $plan, Carbon $startDate, string $billingPeriod): Subscription
  {
    $endDate = $billingPeriod === 'yearly'
      ? $startDate->copy()->addYear()
      : $startDate->copy()->addMonth();

    return $user->subscription()->create([
      'plan_id' => $plan->id,
      'status' => Subscription::STATUS_PENDING,
      'starts_at' => $startDate,
      'ends_at' => $endDate,
      'billing_period' => $billingPeriod,
      'price' => $this->calculatePrice($plan, $billingPeriod),
      'currency' => $plan->currency ?? 'MWK'
    ]);
  }

  public function activateSubscription(Subscription $subscription, string $paychanguReference): void
  {
    DB::transaction(function () use ($subscription, $paychanguReference) {
      // Create renewal record
      $subscription->renewals()->create([
        'amount' => $subscription->price,
        'currency' => $subscription->currency,
        'status' => SubscriptionRenewal::STATUS_SUCCESSFUL,
        'billing_period' => $subscription->billing_period,
        'paychangu_reference' => $paychanguReference
      ]);

      // Activate the subscription
      $subscription->update([
        'status' => Subscription::STATUS_ACTIVE,
        'last_payment_at' => now(),
        'paychangu_reference' => $paychanguReference
      ]);

      // Update user settings
      $subscription->user->settings()->updateOrCreate(
        ['user_id' => $subscription->user_id],
        ['plan_id' => $subscription->plan_id]
      );
    });
  }

  public function cancel(User $user): bool
  {
    return DB::transaction(function () use ($user) {
      $subscription = $user->activeSubscription();

      if (!$subscription) {
        return false;
      }

      // Cancel any pending subscriptions
      $user->subscription()
        ->where('status', Subscription::STATUS_PENDING)
        ->update([
          'status' => Subscription::STATUS_CANCELLED,
          'ends_at' => now()
        ]);

      // Set current subscription to expire at the end of the period
      $subscription->update([
        'status' => Subscription::STATUS_CANCELLED,
        'ends_at' => $subscription->billing_period === 'yearly'
          ? $subscription->starts_at->addYear()
          : $subscription->starts_at->addMonth()
      ]);

      // Schedule reversion to free plan
      $freePlan = Plan::where('price', 0)->first();
      if ($freePlan) {
        $this->createPendingSubscription(
          $user,
          $freePlan,
          $subscription->ends_at,
          'monthly'
        );
      }

      return true;
    });
  }

  private function calculatePrice(Plan $plan, string $billingPeriod): float
  {
    if ($billingPeriod === 'yearly') {
      return $plan->price * 12 * 0.8; // 20% discount for yearly
    }
    return $plan->price;
  }

  /**
   * Handle subscription changes based on plan types and current subscription status
   */
  public function changeSubscription(User $user, Plan $newPlan): Subscription
  {
    return DB::transaction(function () use ($user, $newPlan) {
      $currentSubscription = $user->activeSubscription();

      // If no active subscription or currently on free plan
      if (!$currentSubscription || $currentSubscription->plan->price === 0) {
        return $this->activateNewPlanImmediately($user, $newPlan);
      }

      // Determine if this is an upgrade or downgrade
      $isUpgrade = $newPlan->price > $currentSubscription->plan->price;

      if ($isUpgrade) {
        return $this->handleUpgrade($user, $currentSubscription, $newPlan);
      } else {
        return $this->handleDowngrade($user, $currentSubscription, $newPlan);
      }
    });
  }

  /**
   * Activate a new plan immediately (used for free to paid upgrades)
   */
  private function activateNewPlanImmediately(User $user, Plan $newPlan): Subscription
  {
    // Cancel any existing subscriptions
    $user->subscriptions()
      ->where('status', Subscription::STATUS_ACTIVE)
      ->update(['status' => Subscription::STATUS_CANCELLED]);

    // Create and activate new subscription
    $subscription = Subscription::create([
      'user_id' => $user->id,
      'plan_id' => $newPlan->id,
      'status' => Subscription::STATUS_ACTIVE,
      'starts_at' => now(),
      'ends_at' => now()->addMonth(),
      'last_payment_at' => now(),
    ]);

    // Update user settings
    $user->settings()->update([
      'plan_id' => $newPlan->id
    ]);

    return $subscription;
  }

  /**
   * Handle upgrade to a higher-tier paid plan
   */
  private function handleUpgrade(User $user, Subscription $currentSubscription, Plan $newPlan): Subscription
  {
    // Create new subscription with future start date
    $newSubscription = Subscription::create([
      'user_id' => $user->id,
      'plan_id' => $newPlan->id,
      'status' => Subscription::STATUS_SCHEDULED,
      'starts_at' => $currentSubscription->ends_at,
      'ends_at' => $currentSubscription->ends_at->copy()->addMonth(),
    ]);

    // Grant immediate access to new plan features
    $user->settings()->update([
      'plan_id' => $newPlan->id,
      'effective_plan_id' => $newPlan->id, // New column to track actual available features
      'scheduled_plan_id' => $newPlan->id
    ]);

    return $newSubscription;
  }

  /**
   * Handle downgrade to a lower-tier paid plan
   */
  private function handleDowngrade(User $user, Subscription $currentSubscription, Plan $newPlan): Subscription
  {
    // Create new subscription with future start date
    $newSubscription = Subscription::create([
      'user_id' => $user->id,
      'plan_id' => $newPlan->id,
      'status' => Subscription::STATUS_SCHEDULED,
      'starts_at' => $currentSubscription->ends_at,
      'ends_at' => $currentSubscription->ends_at->copy()->addMonth(),
    ]);

    // Keep current plan features until expiry
    $user->settings()->update([
      'plan_id' => $currentSubscription->plan_id, // Keep current plan
      'effective_plan_id' => $currentSubscription->plan_id, // Keep current features
      'scheduled_plan_id' => $newPlan->id // Track future plan
    ]);

    return $newSubscription;
  }

  /**
   * Cancel subscription
   */
  public function cancelSubscription(User $user): bool
  {
    return DB::transaction(function () use ($user) {
      $subscription = $user->activeSubscription();

      if (!$subscription) {
        return false;
      }

      // Get the free plan
      $freePlan = Plan::where('price', 0)->first();

      // Cancel current subscription at the end of the period
      $subscription->update([
        'status' => Subscription::STATUS_CANCELLED,
        'cancelled_at' => now()
      ]);

      // Schedule switch to free plan
      if ($freePlan) {
        Subscription::create([
          'user_id' => $user->id,
          'plan_id' => $freePlan->id,
          'status' => Subscription::STATUS_SCHEDULED,
          'starts_at' => $subscription->ends_at,
          'ends_at' => null // Free plan has no end date
        ]);

        // Update user settings
        $user->settings()->update([
          'scheduled_plan_id' => $freePlan->id
        ]);
      }

      return true;
    });
  }
}
