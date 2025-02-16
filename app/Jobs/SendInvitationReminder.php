<?php

namespace App\Jobs;

use App\Models\InvitedTeamMember;
use App\Notifications\TeamInvitationReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendInvitationReminder implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public function __construct(
    protected InvitedTeamMember $invitation,
    protected $sendAt
  ) {
    $this->delay = $sendAt;
  }

  public function handle(): void
  {
    // Check if invitation is still valid
    if ($this->invitation->accepted_at || $this->invitation->is_expired) {
      return;
    }

    // Send reminder
    $this->invitation->notify(new TeamInvitationReminder(
      $this->invitation->inviter,
      $this->invitation->team
    ));

    // Update meta data
    $meta = $this->invitation->meta ?? [];
    $meta['reminders'][] = [
      'sent_at' => now()->toDateTimeString(),
      'type' => 'scheduled'
    ];

    $this->invitation->update(['meta' => $meta]);
  }
}
