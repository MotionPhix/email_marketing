<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AudienceAndRecipientSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $user = User::factory()->create([
      'email' => 'user@example.com'
    ]);

    $audiences = \App\Models\Audience::factory(5)
      ->hasRecipients(
        random_int(2, 7),
        fn () => [
          'user_id' => $user->id,
          'status' => fake()->randomElement(['active', 'inactive', 'banned', 'unsubscribed'])
        ]
      ) // Each audience gets between 2 and 5 recipients
      ->create([
        'user_id' => $user->id
      ]);

    // Randomly select some audiences for campaigns
    $selectedAudiences = $audiences->random(3); // Select 3 random audiences

    foreach ($selectedAudiences as $audience) {
      \App\Models\Campaign::factory(2) // Create 1-3 campaigns for each selected audience
      ->create([
        'user_id' => $user->id,
        'audience_id' => $audience->id,
      ]);
    }
  }
}
