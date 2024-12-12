<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\EmailLog;
use App\Models\Recipient;
use App\Models\User;
use SendGrid\Mail\Mail;

class CampaignEmailService
{
  /**
   * Send emails for the given campaign.
   *
   * @param Campaign $campaign
   * @param array $recipients
   */
  public function sendEmails(Campaign $campaign, $recipients, User $user)
  {
    if (!$campaign->template) {
      \Log::error("Campaign [{$campaign->id}] has no template assigned.");
      return;
    }

    foreach ($recipients as $recipient) {
      // Check if the recipient has unsubscribed from the campaign
      if ($recipient->unsubscribedFromCampaign($campaign)) {
        \Log::info("Recipient [{$recipient->email}] has unsubscribed from Campaign [{$campaign->id}]. Skipping email.");
        continue; // Skip sending the email
      }

      $email = $this->prepareEmail($campaign, $recipient, $user);
      $sendgrid = new \SendGrid(config('services.sendgrid.api_key'));

      try {
        $response = $sendgrid->send($email);
        \Log::info($response->body());
      } catch (\Exception $e) {
        \Log::error('SendGrid Error: ' . $e->getMessage());
      }
    }

    // Update campaign status after sending
    $campaign->update(['status' => 'sent']);
  }

  /**
   * Prepare an email for SendGrid.
   *
   * @param Campaign $campaign
   * @param Recipient $recipient
   * @return Mail
   */
  private function prepareEmail(Campaign $campaign, Recipient $recipient, User $user)
  {
    $email = new Mail();
    $email->setFrom(config('mail.from.address'), config('mail.from.name'));
    $email->setSubject($campaign->subject);
    $email->addTo($recipient->email, $recipient->name);

    if ($campaign->template->mode === 'dynamic') {
      $htmlContent = $this->renderTemplate($campaign, [
        'recipient_name' => $recipient->name,
        'first_name' => $recipient->first_name,
        'last_name' => $recipient->last_name,
        'email' => $recipient->email,
        'phone' => $recipient->phone,
        'address' => $recipient->address,
        'campaign_name' => $campaign->name,
        'campaign_subject' => $campaign->subject,
        'unsubscribe_link' => $this->generateUnsubscribeLink($campaign, $recipient),
        'company_name' => $user->company_name,
        'company_email' => $user->email,
        'company_phone' => $user->phone,
        'company_address' => $user->address,
        'sender_name' => $user->name,
      ]);
    } else {
      $htmlContent = $campaign->template->content;
    }

    $email->addContent("text/plain", strip_tags($htmlContent));
    $email->addContent("text/html", $htmlContent);

    $email->addCustomArg('campaign_uuid', $campaign->uuid);
    $email->addCustomArg('user_uuid', auth()->user()->uuid);
    $email->addCategory($campaign->name);

    return $email;
  }

  private function generateUnsubscribeLink(Campaign $campaign, Recipient $recipient)
  {
    return route('campaigns.unsubscribe', ['campaign' => $campaign->uuid, 'recipient' => $recipient->uuid]);
  }

  /**
   * Render the campaign template with recipient data.
   *
   * @param Campaign $campaign
   * @param array $data
   * @return string
   */
  private function renderTemplate(Campaign $campaign, array $data)
  {
    $template = $campaign->template->content; // Retrieve template content from DB

    \Log::debug('before', [$template]);

    // Decode HTML entities
    $decodedTemplate = html_entity_decode($template);

    \Log::debug('after', [$decodedTemplate]);

    // Match placeholders in the template
    preg_match_all('/{{\s*(.*?)\s*}}/', $decodedTemplate, $matches);

    foreach ($matches[1] as $placeholder) {
      $decodedTemplate = str_replace("{{{$placeholder}}}", $data[$placeholder] ?? '', $decodedTemplate);
    }

    // Encode HTML entities
    //$encodedTemplate = htmlentities($decodedTemplate);

    \Log::debug('back', [$decodedTemplate]);

    return $decodedTemplate;
  }
}
