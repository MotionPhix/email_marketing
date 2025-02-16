<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
  /**
   * Handle the User "created" event.
   */
  public function created(User $user): void
  {
    // Create default settings for the user
    $user->settings()->create([
      'preferences' => [
        'language' => 'en',
        'timezone' => 'UTC',
      ],
      'notification_settings' => [
        'email_notifications' => true,
        'in_app_notifications' => true,
      ],
      'email_settings' => [
        'from_name' => null,
        'reply_to' => null,
      ],
      'branding_settings' => [
        'logo_url' => null,
        'primary_color' => '#4F46E5',
        'accent_color' => '#818CF8',
      ],
    ]);
  }

  /**
   * Handle the User "updated" event.
   */
  public function updated(User $user): void
  {
    //
  }

  /**
   * Handle the User "deleted" event.
   */
  public function deleted(User $user): void
  {
    //
  }

  /**
   * Handle the User "restored" event.
   */
  public function restored(User $user): void
  {
    //
  }

  /**
   * Handle the User "force deleted" event.
   */
  public function forceDeleted(User $user): void
  {
    //
  }
}
