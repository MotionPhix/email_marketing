<?php

namespace App\Modules\Campaigns\Jobs;

use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Campaigns\Models\CampaignQueue;
use App\Modules\Campaigns\Models\EmailEvent;
use App\Modules\Campaigns\Models\Recipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Exception;

class SendCampaignEmail implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public function __construct(
    protected CampaignQueue $queueItem
  ) {}

  /*public function handle(): void
  {
    try {
      $this->queueItem->update(['status' => CampaignQueue::STATUS_PROCESSING]);

      $campaign = $this->queueItem->campaign;
      $recipient = $this->queueItem->recipient;

      $this->sendEmail($campaign, $recipient);

      $this->queueItem->update([
        'status' => CampaignQueue::STATUS_SENT,
        'sent_at' => now(),
      ]);

      EmailEvent::create([
        'campaign_id' => $campaign->id,
        'recipient_id' => $recipient->id,
        'event_type' => EmailEvent::EVENT_DELIVERED,
      ]);

    } catch (Exception $e) {
      $this->handleFailure($e);
    }
  }

  protected function sendEmail(Campaign $campaign, Recipient $recipient): void
  {
    // Replace placeholders in content
    $content = $this->replacePlaceholders($campaign->content, $recipient);

    Mail::raw($content, function ($message) use ($campaign, $recipient) {
      $message->to($recipient->email)
        ->subject($campaign->subject)
        ->from(config('mail.from.address'), config('mail.from.name'));

      // Add tracking pixel
      $trackingPixel = $this->generateTrackingPixel($campaign->id, $recipient->id);
      $message->setBody($message->getBody() . $trackingPixel);

      // Convert links to tracking links
      $message->setBody($this->addTrackedLinks($message->getBody(), $campaign->id, $recipient->id));
    });
  }

  protected function replacePlaceholders(string $content, Recipient $recipient): string
  {
    $placeholders = [
      '{{name}}' => $recipient->name,
      '{{email}}' => $recipient->email,
      // Add more placeholders as needed
    ];

    return str_replace(array_keys($placeholders), array_values($placeholders), $content);
  }

  protected function generateTrackingPixel(int $campaignId, int $recipientId): string
  {
    $trackingUrl = route('campaign.track.open', [
      'campaign' => $campaignId,
      'recipient' => $recipientId,
    ]);

    return sprintf(
      '<img src="%s" alt="" width="1" height="1" border="0" style="height:1px!important;width:1px!important;border-width:0!important;margin-top:0!important;margin-bottom:0!important;margin-right:0!important;margin-left:0!important;padding-top:0!important;padding-bottom:0!important;padding-right:0!important;padding-left:0!important"/>',
      $trackingUrl
    );
  }

  protected function addTrackedLinks(string $content, int $campaignId, int $recipientId): string
  {
    return preg_replace_callback(
      '/<a[^>]+href=[\'"](http[^\'"]+)[\'"][^>]*>/i',
      function ($matches) use ($campaignId, $recipientId) {
        $originalUrl = $matches[1];
        $trackingUrl = route('campaign.track.click', [
          'campaign' => $campaignId,
          'recipient' => $recipientId,
          'url' => base64_encode($originalUrl),
        ]);

        return str_replace($originalUrl, $trackingUrl, $matches[0]);
      },
      $content
    );
  }

  protected function handleFailure(Exception $e): void
  {
    $this->queueItem->update([
      'status' => CampaignQueue::STATUS_FAILED,
      'error_message' => $e->getMessage(),
    ]);

    // Log the error
    logger()->error('Campaign email sending failed', [
      'campaign_id' => $this->queueItem->campaign_id,
      'recipient_id' => $this->queueItem->recipient_id,
      'error' => $e->getMessage(),
    ]);

    // Optionally notify admin
    if ($this->attempts() >= $this->tries) {
      // Send notification to admin about permanent failure
    }
  }*/

  public function handle(): void
  {
    try {
      $this->queueItem->update(['status' => CampaignQueue::STATUS_PROCESSING]);

      $campaign = $this->queueItem->campaign;
      $recipient = $this->queueItem->recipient;

      // Create email content with tracking pixels and links
      $content = $this->addTrackingToContent(
        $campaign->content,
        $campaign->id,
        $recipient->id
      );

      Mail::send([], [], function ($message) use ($campaign, $recipient, $content) {
        $message->to($recipient->email, $recipient->name)
          ->subject($campaign->subject)
          ->from($campaign->from_email, $campaign->from_name)
          ->replyTo($campaign->reply_to ?? $campaign->from_email)
          ->html($content);
      });

      $this->queueItem->update([
        'status' => CampaignQueue::STATUS_SENT,
        'sent_at' => now(),
      ]);

      EmailEvent::create([
        'campaign_id' => $campaign->id,
        'recipient_id' => $recipient->id,
        'event_type' => EmailEvent::EVENT_DELIVERED,
      ]);

      $campaign->increment('sent_count');

    } catch (Exception $e) {
      $this->handleFailure($e);
    }
  }

  protected function addTrackingToContent(string $content, string $campaignId, string $recipientId): string
  {
    // Add tracking pixel
    $trackingPixel = sprintf(
      '<img src="%s" alt="" width="1" height="1" />',
      route('campaigns.track.open', ['campaign' => $campaignId, 'recipient' => $recipientId])
    );

    // Add click tracking to links
    $content = preg_replace_callback(
      '/<a\s+(?:[^>]*?\s+)?href=(["\'])(.*?)\1/i',
      function ($matches) use ($campaignId, $recipientId) {
        $url = base64_encode($matches[2]);
        $trackingUrl = route('campaigns.track.click', [
          'campaign' => $campaignId,
          'recipient' => $recipientId,
          'url' => $url,
        ]);
        return '<a href="' . $trackingUrl . '"';
      },
      $content
    );

    return $content . $trackingPixel;
  }

  protected function handleFailure(Exception $e): void
  {
    $this->queueItem->update([
      'status' => CampaignQueue::STATUS_FAILED,
      'error_message' => $e->getMessage(),
    ]);

    EmailEvent::create([
      'campaign_id' => $this->queueItem->campaign_id,
      'recipient_id' => $this->queueItem->recipient_id,
      'event_type' => EmailEvent::EVENT_BOUNCED,
      'metadata' => ['error' => $e->getMessage()],
    ]);

    $this->queueItem->campaign->increment('bounced_count');
  }
}
