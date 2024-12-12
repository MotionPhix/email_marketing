<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'name' => fake()->randomElement(['Free', 'Basic', 'Pro', 'Enterprise']),
      'campaign_limit' =>fake()->numberBetween(1, 100),
      'recipient_limit' =>fake()->numberBetween(100, 10000),
      'email_limit' =>fake()->numberBetween(500, 50000),
      'segment_limit' =>fake()->numberBetween(1, 20),
      'can_schedule_campaigns' =>fake()->boolean,
    ];
  }
}
