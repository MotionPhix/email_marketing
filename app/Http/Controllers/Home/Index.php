<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class Index extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    $plans = Plan::active()
      ->ordered()
      ->get()
      ->map(function ($plan) {
        return [
          'uuid' => $plan->uuid,
          'name' => $plan->name,
          'price' => $plan->price,
          'features' => [
            [
              'uuid' => $plan->uuid, // reuse plan UUID since features are part of plan
              'analytics' => $plan->getAnalyticsLevel(),
              'campaign_limit' => "Up to {$plan->getCampaignLimit()} campaigns",
              'can_schedule_campaigns' => $plan->canScheduleCampaigns(),
              'email_limit' => $plan->features['email_limit'] ?? 'No emails',
              'personalisation' => $plan->hasPersonalisation(),
              'recipient_limit' => "Up to {$plan->getRecipientLimit()} recipients",
              'segment_limit' => $plan->features['segment_limit'] ?? 'No segments',
              'support_type' => $plan->getSupportType(),
            ]
          ]
        ];
      });

    $currentPlan = null;

    if ($request->user()) {
      $userPlan = $request->user()
        ->settings()
        ->with('plan')
        ->first()
        ->plan;

      if ($userPlan) {
        $currentPlan = [
          'uuid' => $userPlan->uuid,
          'name' => $userPlan->name,
          'price' => $userPlan->price,
          'features' => [
            [
              'uuid' => $userPlan->uuid,
              'analytics' => $userPlan->getAnalyticsLevel(),
              'campaign_limit' => "Up to {$userPlan->getCampaignLimit()} campaigns",
              'can_schedule_campaigns' => $userPlan->canScheduleCampaigns(),
              'email_limit' => $userPlan->features['email_limit'] ?? 'No emails',
              'personalisation' => $userPlan->hasPersonalisation(),
              'recipient_limit' => "Up to {$userPlan->getRecipientLimit()} recipients",
              'segment_limit' => $userPlan->features['segment_limit'] ?? 'No segments',
              'support_type' => $userPlan->getSupportType(),
            ]
          ]
        ];
      }
    }

    return Inertia('Home/Index', [
      'plans' => $plans,
      'currentPlan' => $currentPlan,
    ]);
  }
}
