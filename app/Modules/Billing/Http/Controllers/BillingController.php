<?php

namespace App\Modules\Billing\Http\Controllers;

use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Services\BillingService;
use App\Modules\Core\Http\Controllers\ModuleController;
use Illuminate\Http\Request;
use Inertia\Response;

class BillingController extends ModuleController
{
  public function __construct(
    protected BillingService $billingService
  ) {}

  public function index(Request $request): Response
  {
    $plans = Plan::active()
      ->ordered()
      ->get()
      ->map(fn ($plan) => [
        'uuid' => $plan->uuid,
        'name' => $plan->name,
        'price' => $plan->price,
        'formattedPrice' => $plan->getFormattedPrice(),
        'features' => [
          'campaign_limit' => "Up to {$plan->getCampaignLimit()} campaigns",
          'email_limit' => $plan->features['email_limit'],
          'recipient_limit' => "Up to {$plan->getRecipientLimit()} recipients",
          'can_schedule_campaigns' => $plan->canScheduleCampaigns(),
          'personalisation' => $plan->hasPersonalisation(),
        ],
      ]);

    $currentSubscription = $request->user()?->subscription;

    return $this->moduleRender('Index', [
      'plans' => $plans,
      'currentSubscription' => $currentSubscription ? [
        'uuid' => $currentSubscription->uuid,
        'status' => $currentSubscription->status,
        'plan' => [
          'uuid' => $currentSubscription->plan->uuid,
          'name' => $currentSubscription->plan->name,
          'price' => $currentSubscription->plan->price,
        ],
        'trial_ends_at' => $currentSubscription->trial_ends_at,
        'ends_at' => $currentSubscription->ends_at,
      ] : null,
    ]);
  }
}
