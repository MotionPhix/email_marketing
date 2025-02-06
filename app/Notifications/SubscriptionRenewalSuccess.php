<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRenewalSuccess extends Notification implements ShouldQueue
{
  use Queueable;

  protected $subscription;

  public function __construct(Subscription $subscription)
  {
    $this->subscription = $subscription;
  }

  public function via($notifiable): array
  {
    return ['mail', 'database'];
  }

  public function toMail($notifiable): MailMessage
  {
    $amount = number_format($this->subscription->plan->price, 2) . ' MWK';
    $nextBilling = $this->subscription->ends_at->format('F j, Y');

    return (new MailMessage)
      ->subject('Subscription Renewed Successfully')
      ->greeting('Hello ' . $notifiable->first_name . '!')
      ->line('Your subscription has been successfully renewed.')
      ->line("Plan: {$this->subscription->plan->name}")
      ->line("Amount: {$amount}")
      ->line("Next billing date: {$nextBilling}")
      ->action('View Billing Details', route('billing'))
      ->line('Thank you for your continued support!');
  }

  public function toArray($notifiable): array
  {
    return [
      'subscription_id' => $this->subscription->id,
      'plan_name' => $this->subscription->plan->name,
      'amount' => $this->subscription->plan->price,
      'next_billing_date' => $this->subscription->ends_at->toDateString(),
      'message' => 'Your subscription has been successfully renewed.',
      'type' => 'subscription_renewal_success'
    ];
  }
}
