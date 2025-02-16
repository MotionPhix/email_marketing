<?php

namespace App\Listeners;

use App\Events\TeamInvitationCreated;
use App\Jobs\SendInvitationReminder;
use App\Notifications\TeamInvitationReminder;
use Illuminate\Support\Facades\Bus;

class SendInvitationReminders
{
  public function handle(TeamInvitationCreated $event): void
  {
    $delays = [3, 5]; // Days to wait before sending reminders

    $jobs = collect($delays)->map(function ($delay) use ($event) {
      return new SendInvitationReminder(
        $event->invitation,
        now()->addDays($delay)
      );
    });

    Bus::batch($jobs)
      ->allowFailures()
      ->dispatch();
  }
}
