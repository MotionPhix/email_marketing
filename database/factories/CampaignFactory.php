<?php

namespace Database\Factories;

use App\Models\Audience;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
      'uuid' => Str::uuid(),
      'title' => fake()->sentence(2),
      'subject' => fake()->sentence(),
      'status' => 'draft',
      'user_id' => User::factory(),
      'audience_id' => Audience::factory(),
    ];
  }

  public function scheduled(): self
  {
    return $this->state(function (array $attributes) {
      return [
        'status' => 'scheduled',
        'scheduled_at' => now()->addDay(),
      ];
    });
  }
}
