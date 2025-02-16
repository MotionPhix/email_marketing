<?php

namespace App\Listeners;

use App\Events\TeamInvitationAccepted;
use App\Notifications\TeamMemberJoined;
use Illuminate\Support\Facades\Notification;

class NotifyTeamAboutAcceptedInvitation
{
  public function handle(TeamInvitationAccepted $event): void
  {
    // Notify team owner
    $event->invitation->team->owner->notify(
      new TeamMemberJoined($event->acceptedBy, $event->invitation->team)
    );

    // Log the activity
    activity()
      ->performedOn($event->invitation->team)
      ->causedBy($event->acceptedBy)
      ->withProperties([
        'role' => $event->invitation->role
      ])
      ->log('Team member joined');
  }
}
