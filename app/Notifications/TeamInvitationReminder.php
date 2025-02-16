<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitationReminder extends Notification implements ShouldQueue
{
  use Queueable;

  public function __construct(
    protected User $inviter,
    protected Team $team
  ) {}

  public function via($notifiable): array
  {
    return ['mail'];
  }

  public function toMail($notifiable): MailMessage
  {
    return (new MailMessage)
      ->subject('Reminder: You have a pending invitation to ' . $this->team->name)
      ->markdown('emails.team.invitation-reminder', [
        'inviter' => $this->inviter,
        'team' => $this->team,
        'invitation' => $notifiable,
        'daysLeft' => $notifiable->days_until_expiry
      ]);
  }
}
