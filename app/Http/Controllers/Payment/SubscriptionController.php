<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\PayChanguService;
use App\Services\Subscription\SubscriptionRenewalService;
use App\Services\Subscription\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
  public function __construct(
    private SubscriptionService $subscriptionService,
    protected PayChanguService $paychanguService
  ) {}

  public function subscribe(Request $request, Plan $plan)
  {
    try {
      $user = $request->user();
      $billingPeriod = $request->input('billing_period', 'monthly');

      // Create subscription through service
      $subscription = $this->subscriptionService->subscribe($user, $plan, $billingPeriod);

      // Initiate payment
      $payment = $this->paychanguService->initiatePayment(
        $user,
        $plan,
        route('subscription.callback')
      );

      // Store payment reference
      $subscription->update([
        'paychangu_reference' => $payment['reference']
      ]);

      // Redirect to PayChangu payment page
      return redirect($payment['payment_url']);

    } catch (\Exception $e) {
      return back()->with('error', 'Subscription failed: ' . $e->getMessage());
    }
  }

  public function callback(Request $request)
  {
    try {
      $reference = $request->get('reference');
      $payment = $this->paychanguService->verifyPayment($reference);

      $subscription = Subscription::where('paychangu_reference', $reference)->firstOrFail();

      if ($payment['status'] === 'success') {
        $this->subscriptionService->activateSubscription($subscription, $reference);

        return redirect()->route('dashboard')
          ->with('success', 'Subscription activated successfully!');
      }

      return redirect()->route('billing')
        ->with('error', 'Payment failed. Please try again.');

    } catch (\Exception $e) {
      return redirect()->route('billing')
        ->with('error', 'Payment verification failed: ' . $e->getMessage());
    }
  }

  public function webhook(Request $request)
  {
    // Verify webhook signature
    if (!$this->verifyWebhookSignature($request)) {
      abort(403);
    }

    $reference = $request->get('reference');
    $subscription = Subscription::where('paychangu_reference', $reference)->first();

    if (!$subscription) {
      return response()->json(['message' => 'Subscription not found'], 404);
    }

    switch ($request->get('event')) {
      case 'payment.successful':
        $this->subscriptionService->activateSubscription($subscription, $reference);
        break;
      case 'payment.failed':
        $subscription->update([
          'status' => Subscription::STATUS_EXPIRED
        ]);
        break;
    }

    return response()->json(['message' => 'Webhook processed']);
  }

  protected function verifyWebhookSignature(Request $request)
  {
    $signature = $request->header('X-Paychangu-Signature');
    // Implement signature verification logic here
    return true;
  }

  protected function handleSuccessfulPayment(Subscription $subscription)
  {
    $now = Carbon::now();

    $subscription->update([
      'status' => Subscription::STATUS_ACTIVE,
      'starts_at' => $now,
      'ends_at' => $now->addMonth(),
      'last_payment_at' => $now,
    ]);

    $subscription->user->settings()->update([
      'plan_id' => $subscription->plan_id
    ]);
  }

  protected function handleFailedPayment(Subscription $subscription)
  {
    $subscription->update([
      'status' => Subscription::STATUS_EXPIRED,
    ]);
  }

  public function handleRenewalCallback(
    Request $request,
    string $subscription,
    SubscriptionRenewalService $renewalService
  ) {
    try {
      $paymentData = $this->paychanguService->verifyPayment($request->get('reference'));
      $renewalService->handleRenewalCallback($subscription, $paymentData);

      return redirect()->route('billing')
        ->with('success', 'Subscription renewed successfully!');
    } catch (\Exception $e) {
      return redirect()->route('billing')
        ->with('error', 'Subscription renewal failed: ' . $e->getMessage());
    }
  }
}
