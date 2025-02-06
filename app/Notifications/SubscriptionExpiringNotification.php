<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionExpiringNotification extends Notification
{
  use Queueable;

  protected $subscription;

  public function __construct(Subscription $subscription)
  {
    $this->subscription = $subscription;
  }

  public function via($notifiable): array
  {
    return ['mail'];
  }

  public function toMail($notifiable): MailMessage
  {
    return (new MailMessage)
      ->subject('Your Subscription is Expiring Soon')
      ->line("Your {$this->subscription->plan->name} plan is expiring in " .
        $this->subscription->ends_at->diffForHumans() . ".")
      ->line('To ensure uninterrupted service, please renew your subscription.')
      ->action('Renew Now', route('billing'))
      ->line('Thank you for using our service!');
  }
}
