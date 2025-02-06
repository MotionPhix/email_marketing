<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\PayChanguService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillingController extends Controller
{
  public function index(Request $request)
  {
    $user = $request->user();

    return Inertia::render('Billing', [
      'subscription' => $user->activeSubscription()?->load('renewals')->append('formatted_features'),
      'plans' => Plan::active()->ordered()->get(),
      'currentPlan' => $user->settings?->plan
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
