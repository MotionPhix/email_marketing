<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\PayChanguService;
use App\Services\Subscription\SubscriptionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class BillingController extends Controller
{
  public function __construct(private SubscriptionService $subscriptionService)
  {}

  public function index(Request $request)
  {
    $user = $request->user();
    $activeSubscription = $user->activeSubscription();
    $pendingSubscription = $user->pendingSubscription;

    return Inertia::render('Billing', [
      'subscription' => $activeSubscription
        ? $activeSubscription->load('renewals')->append('formatted_features')
        : null,

      'pendingSubscription' => $pendingSubscription
        ? $pendingSubscription->load('plan')->append('formatted_features')
        : null,

      'plans' => Plan::active()->ordered()->get(),

      'currentPlan' => $user->settings?->plan,

      'canChangeSubscription' => $user->canChangeSubscription()
    ]);
  }

  public function toggleAutoRenew(Request $request)
  {
    $subscription = $request->user()->activeSubscription();

    if (!$subscription) {
      return back()->with('error', 'No active subscription found.');
    }

    $subscription->update([
      'auto_renew' => $request->boolean('auto_renew')
    ]);

    return back()->with('success', 'Auto-renewal settings updated.');
  }

  public function changePlan(Request $request)
  {
    $request->validate([
      'plan' => 'required|exists:plans,uuid'
    ]);

    $user = $request->user();
    $newPlan = Plan::where('uuid', $request->plan)->firstOrFail();
    $currentSubscription = $user->activeSubscription();

    // If downgrading, schedule the change for the end of the current period
    if ($currentSubscription && $newPlan->price < $currentSubscription->plan->price) {
      $currentSubscription->update([
        'ends_at' => now()->endOfDay(),
        'cancelled_at' => now(),
        'status' => 'cancelled'
      ]);

      // Create new subscription starting after current one ends
      $user->subscriptions()->create([
        'plan_id' => $newPlan->id,
        'status' => 'pending',
        'starts_at' => $currentSubscription->ends_at,
        'ends_at' => $currentSubscription->ends_at->addMonth(),
        'auto_renew' => $currentSubscription->auto_renew
      ]);

      return back()->with('success', 'Plan change scheduled for your next billing cycle.');
    }

    // If upgrading, process immediately
    try {
      $payment = app(PayChanguService::class)->initiatePayment(
        $user,
        $newPlan,
        route('subscription.callback')
      );

      // Create new subscription
      $subscription = $user->subscriptions()->create([
        'plan_id' => $newPlan->id,
        'status' => 'pending',
        'paychangu_reference' => $payment['reference']
      ]);

      return redirect($payment['payment_url']);
    } catch (\Exception $e) {
      return back()->with('error', 'Failed to process plan change: ' . $e->getMessage());
    }
  }

  public function subscribe(Request $request)
  {
    $validated = $request->validate([
      'plan_id' => 'required|exists:plans,id',
      'billing_period' => 'required|in:monthly,yearly'
    ]);

    try {
      $subscription = $this->subscriptionService->subscribe(
        $request->user(),
        Plan::findOrFail($validated['plan_id']),
        $validated['billing_period']
      );

      return back()->with('success', 'Successfully subscribed to plan.');
    } catch (\Exception $e) {
      throw ValidationException::withMessages([
        'subscription' => 'Failed to process subscription: ' . $e->getMessage()
      ]);
    }
  }

  public function cancel(Request $request)
  {
    try {
      $this->subscriptionService->cancel($request->user());
      return back()->with('success', 'Subscription cancelled successfully.');
    } catch (\Exception $e) {
      throw ValidationException::withMessages([
        'subscription' => 'Failed to cancel subscription: ' . $e->getMessage()
      ]);
    }
  }

  public function upgrade(Request $request)
  {
    $validated = $request->validate([
      'plan_id' => 'required|exists:plans,id'
    ]);

    $subscription = $request->user()->activeSubscription();
    if (!$subscription) {
      throw ValidationException::withMessages([
        'subscription' => 'No active subscription found.'
      ]);
    }

    try {
      $this->subscriptionService->upgrade(
        $subscription,
        Plan::findOrFail($validated['plan_id'])
      );
      return back()->with('success', 'Plan upgraded successfully.');
    } catch (\Exception $e) {
      throw ValidationException::withMessages([
        'subscription' => 'Failed to upgrade plan: ' . $e->getMessage()
      ]);
    }
  }

  public function downloadInvoice(Request $request, string $reference = null)
  {
    $user = $request->user();
    $subscription = $reference
      ? $user->subscriptions()->where('paychangu_reference', $reference)->firstOrFail()
      : $user->activeSubscription();

    if (!$subscription) {
      return back()->with('error', 'No subscription found.');
    }

    // Generate and return invoice PDF
    $pdf = PDF::loadView('invoices.subscription', [
      'subscription' => $subscription,
      'user' => $user
    ]);

    return $pdf->download("invoice-{$subscription->paychangu_reference}.pdf");
  }
}
