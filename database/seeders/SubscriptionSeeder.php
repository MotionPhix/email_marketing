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
      'campaign_limit' => 2,
      'recipient_limit' => 50,
      'email_limit' => 100,
      'segment_limit' => 1,
      'can_schedule_campaigns' => false,
    ]);

    Subscription::create([
      'name' => 'Basic',
      'campaign_limit' => 10,
      'recipient_limit' => 100,
      'email_limit' => 500,
      'segment_limit' => 5,
      'can_schedule_campaigns' => true,
    ]);

    Subscription::create([
      'name' => 'Pro',
      'campaign_limit' => 50,
      'recipient_limit' => 500,
      'email_limit' => 1000,
      'segment_limit' => 10,
      'can_schedule_campaigns' => true,
    ]);

    Subscription::create([
      'name' => 'Enterprise',
      'campaign_limit' => 100,
      'recipient_limit' => 1500,
      'email_limit' => 5000,
      'segment_limit' => 20,
      'can_schedule_campaigns' => true,
    ]);
  }
}
