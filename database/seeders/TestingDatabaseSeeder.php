<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use App\Models\User;
use App\Models\Subscription;

class TestingDatabaseSeeder extends Seeder
{
  public function run()
  {
    // Create basic plans
    $freePlan = Plan::factory()->free()->create();
    $proPlan = Plan::factory()->pro()->create();

    // Create test user
    $user = User::factory()->create([
      'email' => 'test@example.com'
    ]);

    // Create active subscription for test user
    Subscription::factory()
      ->active()
      ->for($proPlan)
      ->for($user)
      ->create();
  }
}
