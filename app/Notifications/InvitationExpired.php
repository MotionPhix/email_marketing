<?php

namespace App\Notifications;

use App\Models\InvitedTeamMember;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitationExpired extends Notification implements ShouldQueue
{
  use Queueable;

  /**
   * Create a new notification instance.
   */
  public function __construct(
    protected InvitedTeamMember $invitation
  ) {}

  /**
   * Get the notification's delivery channels.
   */
  public function via(object $notifiable): array
  {
    return ['mail', 'database'];
  }

  /**
   * Get the mail representation of the notification.
   */
  public function toMail(object $notifiable): MailMessage
  {
    return (new MailMessage)
      ->subject('Team Invitation Expired')
      ->markdown('emails.team.invitation-expired', [
        'invitation' => $this->invitation,
        'notifiable' => $notifiable,
      ]);
  }

  /**
   * Get the array representation of the notification.
   */
  public function toArray(object $notifiable): array
  {
    return [
      'invitation_id' => $this->invitation->id,
      'team_id' => $this->invitation->team_id,
      'team_name' => $this->invitation->team->name,
      'invitee_email' => $this->invitation->email,
      'expired_at' => $this->invitation->expires_at->toDateTimeString(),
      'type' => 'invitation_expired'
    ];
  }
}
