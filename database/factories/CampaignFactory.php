<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Campaign>
 */
class CampaignFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'title' => fake()->sentence(2),
      'subject' => fake()->sentence(),
      'status' => 'draft',
      'user_id' => null, // Set in the seeder
      'audience_id' => null, // Set in the seeder
    ];
  }
}
