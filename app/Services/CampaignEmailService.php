<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Http;

class CampaignEmailService
{
  /**
   * Send emails for the given campaign.
   *
   * @param Campaign $campaign
   * @param array $recipients
   */
  public function sendEmails(Campaign $campaign, $recipients)
  {
    if (!$campaign->template) {
      \Log::error("Campaign [{$campaign->id}] has no template assigned.");
      return;
    }

    $emails = $this->prepareEmails($campaign, $recipients);

    // Send emails in batches
    foreach (array_chunk($emails, 200) as $batch) {
      $this->sendWithSendGrid($batch, $campaign);
    }

    // Update campaign status after sending
    $campaign->update(['status' => 'sent']);
  }

  /**
   * Prepare emails to be sent.
   *
   * @param Campaign $campaign
   * @param $recipients
   * @return array
   */
  private function prepareEmails(Campaign $campaign, $recipients)
  {
    return $recipients->map(function ($recipient) use ($campaign) {
      $data = [
        'name' => $recipient->name,
        'email' => $recipient->email,
      ];

      $htmlContent = TemplateRenderer::render($campaign->template->content, $data);

      return [
        'to' => [['email' => $recipient->email]],
        'subject' => $campaign->subject,
        'custom_args' => [
          'campaign_id' => $campaign->uuid,
          'recipient_id' => $recipient->uuid,
        ],
        'categories' => ['campaign-' . $campaign->uuid],
        'content' => [
          [
            'type' => 'text/html',
            'value' => $htmlContent,
          ],
          [
            'type' => 'text/plain',
            'value' => strip_tags($htmlContent), // Fallback plain text content
          ],
        ],
      ];
    })->toArray();
  }

  /**
   * Send the emails using SendGrid.
   *
   * @param array $emails
   * @param Campaign $campaign
   */
  private function sendWithSendGrid(array $emails, Campaign $campaign)
  {
    $response = Http::withHeaders([
      'Authorization' => 'Bearer ' . config('services.sendgrid.api_key'),
    ])->post('https://api.sendgrid.com/v3/mail/send', [
      'personalizations' => $emails,
      'from' => [
        'email' => config('mail.from.address'),
        'name' => config('mail.from.name'),
      ],
    ]);

    if (!$response->successful()) {
      \Log::error('SendGrid Error', $response->json());

      foreach ($emails as $email) {
        EmailLog::create([
          'campaign_id' => $campaign->id,
          'recipient_email' => $email['to'][0]['email'],
          'status' => 'failed',
          'error' => $response->json()['errors'][0]['message'] ?? 'Unknown error',
        ]);
      }
    } else {
      foreach ($emails as $email) {
        EmailLog::create([
          'campaign_id' => $campaign->id,
          'recipient_email' => $email['to'][0]['email'],
          'status' => 'sent',
        ]);
      }
    }
  }
}
