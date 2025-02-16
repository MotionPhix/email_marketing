<?php

namespace App\Events;

use App\Models\InvitedTeamMember;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeamInvitationUpdated
{
  use Dispatchable, SerializesModels;

  public function __construct(
    public InvitedTeamMember $invitation,
    public array $changes
  ) {}
}
