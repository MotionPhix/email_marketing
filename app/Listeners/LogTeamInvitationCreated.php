<?php

namespace App\Listeners;

use App\Events\TeamInvitationCreated;
use Illuminate\Support\Facades\Log;

class LogTeamInvitationCreated
{
  public function handle(TeamInvitationCreated $event): void
  {
    activity()
      ->performedOn($event->invitation)
      ->causedBy($event->invitation->inviter)
      ->withProperties([
        'team' => $event->invitation->team->name,
        'email' => $event->invitation->email,
        'role' => $event->invitation->role
      ])
      ->log('Team invitation created');

    Log::info('Team invitation created', [
      'invitation_id' => $event->invitation->id,
      'team_id' => $event->invitation->team_id,
      'inviter_id' => $event->invitation->user_id,
      'email' => $event->invitation->email
    ]);
  }
}
