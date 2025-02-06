<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
  public function handle(Request $request, Closure $next, ...$guards): Response
  {
    $user = $request->user();

    if (!$user) {
      return redirect()->route('login');
    }

    $subscription = $user->activeSubscription();

    // Check if user has no subscription and is not in trial period
    if (!$subscription && !$user->onTrial()) {
      return redirect()->route('billing')
        ->with('error', 'Please subscribe to a plan to access this feature.');
    }

    // Check if subscription has expired
    if ($subscription && $subscription->hasExpired()) {
      return redirect()->route('billing')
        ->with('error', 'Your subscription has expired. Please renew to continue.');
    }

    // Add subscription to the request for feature limitation checks
    $request->attributes->set('subscription', $subscription);

    return $next($request);
  }
}
