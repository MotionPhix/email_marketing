<?php

namespace App\Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Http\Requests\SubscriptionRequest;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
  public function __construct(
    protected BillingService $billingService
  ) {}

  public function store(SubscriptionRequest $request): RedirectResponse
  {
    try {
      $plan = Plan::where('uuid', $request->plan_uuid)->firstOrFail();

      $result = $this->billingService->initiateSubscription([
        'user_id' => $request->user()->id,
        'plan_id' => $plan->id,
      ]);

      // Redirect to PayChangu payment page
      return redirect()->away($result['payment_url']);
    } catch (\Exception $e) {
      report($e);

      return redirect()->route('billing.index')
        ->with('error', 'Failed to create subscription. Please try again.');
    }
  }

  public function destroy(string $uuid): RedirectResponse
  {
    try {
      $subscription = auth()->user()->subscription;

      if ($subscription && $subscription->uuid === $uuid) {
        $this->billingService->cancelSubscription($subscription);
      }

      return redirect()->route('billing.index')
        ->with('success', 'Subscription has been cancelled.');
    } catch (\Exception $e) {
      report($e);

      return redirect()->route('billing.index')
        ->with('error', 'Failed to cancel subscription. Please try again.');
    }
  }

  public function success(Request $request): RedirectResponse
  {
    return redirect()->route('billing.index')
      ->with('success', 'Payment successful! Your subscription is now active.');
  }

  public function cancelled(Request $request): RedirectResponse
  {
    return redirect()->route('billing.index')
      ->with('info', 'Payment was cancelled.');
  }
}
