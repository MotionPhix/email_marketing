<?php

namespace App\Notifications;

use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamMemberJoined extends Notification implements ShouldQueue
{
  use Queueable;

  /**
   * Create a new notification instance.
   */
  public function __construct(
    protected User $newMember,
    protected Team $team
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
      ->subject('New Team Member Joined')
      ->markdown('emails.team.member-joined', [
        'newMember' => $this->newMember,
        'team' => $this->team,
        'notifiable' => $notifiable,
      ]);
  }

  /**
   * Get the array representation of the notification.
   */
  public function toArray(object $notifiable): array
  {
    return [
      'team_id' => $this->team->id,
      'team_name' => $this->team->name,
      'member_id' => $this->newMember->id,
      'member_name' => $this->newMember->name,
      'member_email' => $this->newMember->email,
      'type' => 'team_member_joined'
    ];
  }
}
