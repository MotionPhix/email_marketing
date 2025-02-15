<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Subscriber;
use SendGrid\Mail\Mail;
use SendGrid\Mail\Personalization;

class CampaignService
{
  public function __construct(
    protected EmailPersonalizationService $emailPersonalization,
    protected SendGridService $sendGrid
  ) {}

  public function sendCampaign(Campaign $campaign)
  {
    $email = new Mail();
    $email->setFrom($campaign->from_email, $campaign->from_name);
    $email->setSubject($campaign->subject);
    $email->addHeader('X-Campaign-ID', $campaign->id);

    // Get subscribers
    $subscribers = Subscriber::whereIn('list_id', $campaign->recipient_lists)
      ->get();

    foreach ($subscribers as $subscriber) {
      $personalization = new Personalization();
      $personalization->addTo($subscriber->email);

      // Add custom variables
      $personalization->addDynamicTemplateData('subscriber', [
        'first_name' => $subscriber->first_name,
        'last_name' => $subscriber->last_name,
        'email' => $subscriber->email,
      ]);

      $personalization->addDynamicTemplateData('campaign', [
        'name' => $campaign->name,
        'subject' => $campaign->subject,
      ]);

      $email->addPersonalization($personalization);
    }

    // Process template content
    $content = $this->emailPersonalization->parseTemplate(
      $campaign->content,
      [] // Base variables, personalization handled by SendGrid
    );

    $email->addContent('text/html', $content);

    // Send via SendGrid
    return $this->sendGrid->send($email);
  }
}
