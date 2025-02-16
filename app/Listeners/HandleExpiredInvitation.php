<?php

namespace App\Listeners;

use App\Events\TeamInvitationExpired;
use App\Notifications\InvitationExpired;
use App\Services\ActivityLogger;

class HandleExpiredInvitation
{
  public function handle(TeamInvitationExpired $event): void
  {
    // Update invitation status
    $event->invitation->update(['status' => 'expired']);

    // Notify the inviter
    $event->invitation->inviter->notify(
      new InvitationExpired($event->invitation)
    );

    // Log the expiration
    ActivityLogger::logInvitation(
      $event->invitation,
      'Team invitation expired',
      [
        'expired_at' => now()->toDateTimeString(),
        'days_active' => $event->invitation->invited_at->diffInDays(now()),
        'reminder_count' => count($event->invitation->meta['reminders'] ?? [])
      ]
    );
  }
}
