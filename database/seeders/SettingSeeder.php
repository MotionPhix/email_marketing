<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $users = User::all();
    $plan = Plan::where('name', 'Free')->first();

    foreach ($users as $user) {
      Setting::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'email_from_address' => $user->email,
        'email_from_name' => $user->name,
        'sender_name' => fake()->name,
        'timezone' => fake()->randomElement(['Africa/Blantyre', 'WET', 'GMT', 'CAT', 'EAT']),
      ]);
    }
  }
}
