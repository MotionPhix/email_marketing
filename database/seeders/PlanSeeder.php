<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
  public function run(): void
  {
    $plans = [
      [
        'name' => 'Free',
        'description' => 'Perfect for getting started with email marketing',
        'price' => 0,
        'is_active' => true,
        'is_featured' => false,
        'sort_order' => 1,
        'features' => [
          'email_limit' => '100 emails per month',
          'segment_limit' => '1 segment',
          'campaign_limit' => '2 campaigns',
          'recipient_limit' => '100 recipients',
          'analytics' => 'Basic analytics',
          'support_type' => 'Community support',
          'personalisation' => false,
          'can_schedule_campaigns' => false,
        ]
      ],
      [
        'name' => 'Basic',
        'description' => 'Great for small businesses starting to grow',
        'price' => 30,
        'is_active' => true,
        'is_featured' => false,
        'sort_order' => 2,
        'features' => [
          'email_limit' => '500 emails per month',
          'segment_limit' => '5 segments',
          'campaign_limit' => '10 campaigns',
          'recipient_limit' => '500 recipients',
          'analytics' => 'Basic analytics',
          'support_type' => 'Essential support',
          'personalisation' => false,
          'can_schedule_campaigns' => false,
        ]
      ],
      [
        'name' => 'Pro',
        'description' => 'Perfect for growing businesses',
        'price' => 60,
        'is_active' => true,
        'is_featured' => true,
        'sort_order' => 3,
        'features' => [
          'email_limit' => '1000 emails per month',
          'segment_limit' => '10 segments',
          'campaign_limit' => '50 campaigns',
          'recipient_limit' => '750 recipients',
          'analytics' => 'Advanced analytics',
          'support_type' => 'Priority support',
          'personalisation' => true,
          'can_schedule_campaigns' => true,
        ]
      ],
      [
        'name' => 'Enterprise',
        'description' => 'For large organizations with advanced needs',
        'price' => 120,
        'is_active' => true,
        'is_featured' => false,
        'sort_order' => 4,
        'features' => [
          'email_limit' => '5000 emails per month',
          'segment_limit' => '20 segments',
          'campaign_limit' => '100 campaigns',
          'recipient_limit' => '1500 recipients',
          'analytics' => 'Full analytics',
          'support_type' => 'Dedicated support',
          'personalisation' => true,
          'can_schedule_campaigns' => true,
        ]
      ],
    ];

    foreach ($plans as $plan) {
      Plan::create($plan);
    }
  }
}
