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
      $sendgrid = new \SendGrid(config('services.sendgrid.apikey'));

      try {
        $response = $sendgrid->send($email);
        $this->logEmailStatus($campaign, $recipient, $response->statusCode() === 202 ? 'sent' : 'failed');
      } catch (\Exception $e) {
        \Log::error('SendGrid Error: ' . $e->getMessage());
        $this->logEmailStatus($campaign, $recipient, 'failed', $e->getMessage());
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

    if ($campaign->template->type === 'dynamic') {
      $htmlContent = $this->renderTemplate($campaign, [
        'name' => $recipient->name,
        'first_name' => $recipient->first_name,
        'last_name' => $recipient->last_name,
        'email' => $recipient->email,
        'phone' => $recipient->phone,
        'address' => $recipient->address,
        'campaign_name' => $campaign->name,
        'campaign_subject' => $campaign->subject,
        'unsubscribe_link' => $this->generateUnsubscribeLink($recipient),
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

    $email->addCustomArg('campaign_id', $campaign->uuid);
    $email->addCustomArg('recipient_id', $recipient->uuid);
    $email->addCategory('campaign-' . $campaign->uuid);

    return $email;
  }

  private function generateUnsubscribeLink(Recipient $recipient)
  {
    return route('campaigns.unsubscribe', ['recipient' => $recipient->uuid]);
  }

  private function getHtmlContent(Campaign $campaign, array $data)
  {
    if ($campaign->template->type === 'dynamic') {
      return $this->renderTemplate($campaign, $data);
    }

    return $campaign->template->content;
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

    // Match placeholders in the template
    preg_match_all('/{{(.*?)}}/', $template, $matches);

    foreach ($matches[1] as $placeholder) {
      $template = str_replace("{{{$placeholder}}}", $data[$placeholder] ?? '', $template);
    }

    return $template;
  }

  /**
   * Log the email status.
   *
   * @param Campaign $campaign
   * @param $recipient
   * @param string $status
   * @param string|null $error
   */
  private function logEmailStatus(Campaign $campaign, $recipient, $status, $error = null)
  {
    EmailLog::create([
      'campaign_id' => $campaign->id,
      'recipient_email' => $recipient->email,
      'status' => $status,
      'error' => $error,
    ]);
  }
}
