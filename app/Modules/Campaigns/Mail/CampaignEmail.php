<?php

namespace App\Modules\Campaigns\Mail;

use App\Modules\Campaigns\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CampaignEmail extends Mailable
{
  use Queueable, SerializesModels;

  public function __construct(
    private readonly Campaign $campaign,
    private readonly array $subscriber
  ) {}

  public function build(): self
  {
    return $this
      ->from($this->campaign->from_email, $this->campaign->from_name)
      ->replyTo($this->campaign->reply_to ?? $this->campaign->from_email)
      ->subject($this->campaign->subject)
      ->html($this->parseContent());
  }

  private function parseContent(): string
  {
    $content = $this->campaign->content;

    // Replace personalization tags
    foreach ($this->subscriber['fields'] as $key => $value) {
      $content = str_replace("{{$key}}", $value, $content);
    }

    // Add tracking pixel
    $trackingPixel = '<img src="' . route('campaigns.track-open', [
        'campaign' => $this->campaign->uuid,
        'subscriber' => base64_encode($this->subscriber['email'])
      ]) . '" alt="" width="1" height="1" />';

    return $content . $trackingPixel;
  }
}
