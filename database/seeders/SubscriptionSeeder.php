<?php

namespace Database\Seeders;

use App\Models\Subscription;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Subscription::create([
      'name' => 'Free',
      'price' => 0,
      'features' => [
        'campaign_limit' => 'Up to 2 campaigns',
        'recipient_limit' => 'Up to 100 recipients',
        'email_limit' => 'up to 100 emails per month',
        'segment_limit' => 'Limited to 1 segment',
        'can_schedule_campaigns' => 'No scheduled campaigns',
        'support_type' => 'No support',
        'analytics' => 'No analytics',
        'personalisation' => 'No personalisation'
      ]
    ]);

    Subscription::create([
      'name' => 'Basic',
      'price' => 30,
      'features' => [
        'campaign_limit' => 'Up to 10 campaigns',
        'recipient_limit' => 'Up to 500 recipients',
        'email_limit' => 'Up to 500 emails per month',
        'segment_limit' => 'Up to 5 segments',
        'can_schedule_campaigns' => 'No scheduled campaigns',
        'support_type' => 'Essential support',
        'analytics' => 'Basic analytics',
        'personalisation' => 'No personalisation'
      ]
    ]);

    Subscription::create([
      'name' => 'Pro',
      'price' => 60,
      'features' => [
        'campaign_limit' => 'Up to 50 campaigns',
        'recipient_limit' => 'Up to 750 recipients',
        'email_limit' => 'Up to 1000 emails per month',
        'segment_limit' => 'Up to 10 segments',
        'analytics' => 'Minimal analytic reports',
        'can_schedule_campaigns' => 'Scheduled campaigns',
        'support_type' => 'Minimal support',
        'personalisation' => 'Full personalisation'
      ]
    ]);

    Subscription::create([
      'name' => 'Enterprise',
      'price' => 120,
      'features' => [
        'campaign_limit' => 'Up to 100 campaigns',
        'recipient_limit' => 'Up to 1500 recipients',
        'email_limit' => 'Up to 5000 emails per month',
        'segment_limit' => 'Up to 20 segments',
        'analytics' => 'Full analytic reports',
        'support_type' => 'Dedicated support via phone/email',
        'can_schedule_campaigns' => 'Scheduled campaigns',
        'personalisation' => 'Full personalisation'
      ]
    ]);
  }
}
