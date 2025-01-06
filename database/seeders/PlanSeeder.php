<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Plan::create([
      'name' => 'Free',
      'price' => 0,
      'features' => [
        'campaign_limit' => '2 campaigns',
        'recipient_limit' => '100 recipients',
        'email_limit' => '100 emails per month',
        'segment_limit' => '1 segment',
      ]
    ]);

    Plan::create([
      'name' => 'Basic',
      'price' => 30,
      'features' => [
        'campaign_limit' => '10 campaigns',
        'recipient_limit' => '500 recipients',
        'email_limit' => '500 emails per month',
        'segment_limit' => '5 segments',
        'support_type' => 'Essential support',
        'analytics' => 'Basic analytics',
      ]
    ]);

    Plan::create([
      'name' => 'Pro',
      'price' => 60,
      'features' => [
        'campaign_limit' => '50 campaigns',
        'recipient_limit' => '750 recipients',
        'email_limit' => '1000 emails per month',
        'segment_limit' => '10 segments',
        'analytics' => 'Minimal analytic reports',
        'can_schedule_campaigns' => 'Scheduled campaigns',
        'support_type' => 'Minimal support',
        'personalisation' => 'Full personalisation'
      ]
    ]);

    Plan::create([
      'name' => 'Enterprise',
      'price' => 120,
      'features' => [
        'campaign_limit' => '100 campaigns',
        'recipient_limit' => '1500 recipients',
        'email_limit' => '5000 emails per month',
        'segment_limit' => '20 segments',
        'analytics' => 'Full analytic reports',
        'support_type' => 'Dedicated support',
        'can_schedule_campaigns' => 'Scheduled campaigns',
        'personalisation' => 'Full personalisation'
      ]
    ]);
  }
}
