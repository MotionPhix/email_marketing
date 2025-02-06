<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRenewalFailed extends Notification implements ShouldQueue
{
  use Queueable;

  protected $subscription;
  protected $reason;

  public function __construct(Subscription $subscription, string $reason = null)
  {
    $this->subscription = $subscription;
    $this->reason = $reason ?? 'The payment could not be processed.';
  }

  public function via($notifiable): array
  {
    return ['mail', 'database'];
  }

  public function toMail($notifiable): MailMessage
  {
    $amount = number_format($this->subscription->plan->price, 2) . ' MWK';
    $graceEndDate = now()->addDays(3)->format('F j, Y');

    return (new MailMessage)
      ->subject('Subscription Renewal Failed')
      ->greeting('Hello ' . $notifiable->first_name . '!')
      ->error()
      ->line('We were unable to renew your subscription.')
      ->line("Plan: {$this->subscription->plan->name}")
      ->line("Amount: {$amount}")
      ->line("Reason: {$this->reason}")
      ->line("To prevent service interruption, please update your payment information before {$graceEndDate}.")
      ->action('Update Payment Method', route('billing'))
      ->line('If you need assistance, please contact our support team.')
      ->line('Your account will remain active during this grace period, but will be limited if payment is not received.');
  }

  public function toArray($notifiable): array
  {
    return [
      'subscription_id' => $this->subscription->id,
      'plan_name' => $this->subscription->plan->name,
      'amount' => $this->subscription->plan->price,
      'reason' => $this->reason,
      'grace_period_ends' => now()->addDays(3)->toDateString(),
      'message' => 'Your subscription renewal payment has failed.',
      'type' => 'subscription_renewal_failed'
    ];
  }
}
