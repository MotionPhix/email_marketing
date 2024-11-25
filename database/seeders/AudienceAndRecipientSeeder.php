<?php

namespace Database\Seeders;

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

    \App\Models\Audience::factory(5)
      ->hasRecipients(
        2,
        fn () => ['user_id' => $user->id]
      ) // Each audience gets between 3 and 7 recipients
      ->create([
        'user_id' => $user->id
      ]);
  }
}
