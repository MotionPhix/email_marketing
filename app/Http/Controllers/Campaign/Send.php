<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\EmailLog;
use App\Services\TemplateRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Send extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Campaign $campaign = null)
  {
    if (!$campaign) {
      if (!$request->template_id) {
        return back()->with('flash', [
          'bannerStyle' => 'danger',
          'banner' => 'Template not set for this campaign.',
        ]);
      }

      $storeController = app(Store::class);
      $campaign = $storeController($request);

      if (!$request->scheduled_at) {
        $this->sendCampaign($campaign);
      }
    }

    return redirect()
      ->route('campaigns.index')
      ->with('success', 'Campaign has been scheduled!');
  }

  /**
   * Send the campaign emails.
   */
  private function sendCampaign(Campaign $campaign)
  {
    if (!$campaign->template) {
      return redirect()
        ->route('campaigns.index')
        ->withErrors('Template not set for this campaign.');
    }

    $recipients = $campaign->audience->recipients;

    $emails = $recipients->map(function ($recipient) use ($campaign) {
      $data = [
        'name' => $recipient->name,
        'email' => $recipient->email,
        'unsubscribe_link' => route('unsubscribe', ['email' => $recipient->email]),
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
        ],
      ];
    })->toArray();

    // Send in batches for better performance
    foreach (array_chunk($emails, 200) as $batch) {
      $this->sendWithSendGrid($batch, $campaign);
    }

    $campaign->update(['status' => 'sent']);

    return redirect()
      ->route('campaigns.index')
      ->with('success', 'Campaign was sent successfully!');
  }

  /**
   * Send emails via SendGrid.
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
      logger('SendGrid Error:', $response->json());
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
