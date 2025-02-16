<?php

namespace App\Listeners;

use App\Events\EmailSent;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateEmailQuota implements ShouldQueue
{
  public function handle(EmailSent $event): void
  {
    $event->user->incrementEmailQuota($event->emailCount);
  }
}
