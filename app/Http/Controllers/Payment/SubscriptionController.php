<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\PayChanguService;
use App\Services\Subscription\SubscriptionRenewalService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
  protected $paychanguService;

  public function __construct(PayChanguService $paychanguService)
  {
    $this->paychanguService = $paychanguService;
  }

  public function subscribe(Request $request, Plan $plan)
  {
    try {
      $user = $request->user();

      // Check if user already has an active subscription
      $activeSubscription = $user->subscriptions()
        ->where('status', Subscription::STATUS_ACTIVE)
        ->first();

      if ($activeSubscription) {
        return back()->with('error', 'You already have an active subscription.');
      }

      // Create pending subscription
      $subscription = Subscription::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'status' => Subscription::STATUS_PENDING,
      ]);

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
        $now = Carbon::now();

        $subscription->update([
          'status' => Subscription::STATUS_ACTIVE,
          'starts_at' => $now,
          'ends_at' => $now->addMonth(),
          'last_payment_at' => $now,
        ]);

        // Update user settings with new plan
        $subscription->user->settings()->update([
          'plan_id' => $subscription->plan_id
        ]);

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
        $this->handleSuccessfulPayment($subscription);
        break;
      case 'payment.failed':
        $this->handleFailedPayment($subscription);
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
