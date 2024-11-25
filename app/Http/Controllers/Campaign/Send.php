<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\EmailLog;
use App\Services\TemplateRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class Send extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Campaign $campaign = null)
  {
    // Ensure the user owns the campaign
    // $this->authorize('send', $campaign);

    if (! $campaign) {

      if (! $request->template_id) {
        return back()->with('flash', [
          'bannerStyle' => 'danger',
          'banner' => 'Template not set for this campaign.'
        ]);
      }

      // Forward the request to the Store controller
      $storeController = app(Store::class);
      $campaign = $storeController($request);

      if (! $request->scheduled_at) {

        $this->sendCampaign($campaign);

      }

    }

    return redirect()
      ->back('campaigns.index')
      ->with('success', 'Campaign has been scheduled!');
  }

  private function sendCampaign(Campaign $campaign)
  {
    if (! $campaign->template) {
      return redirect()
        ->route('campaigns.index')
        ->withErrors('Template not set for this campaign.');
    }

    $recipients = $campaign->audience->recipients;

    foreach ($recipients as $recipient) {
      $data = [
        'name' => $recipient->name,
        'email' => $recipient->email,
        'unsubscribe_link' => route('unsubscribe', ['email' => $recipient->email]),
      ];

      $htmlContent = TemplateRenderer::render($campaign->template->content, $data);
      $this->sendWithSendGrid($campaign, $recipient->email, $campaign->subject, $htmlContent);
    }

    $campaign->update(['status' => 'sent']);

    return redirect()
      ->back('campaigns.index')
      ->with('success', 'Campaign was sent successfully!');
  }

  private function sendWithSendGrid($campaign, $email, mixed $subject, mixed $htmlContent)
  {
    $response = Http::withHeaders([
      'Authorization' => 'Bearer ' . config('services.sendgrid.api_key'),
    ])->post('https://api.sendgrid.com/v3/mail/send', [
      'personalizations' => [
        [
          'to' => [
            ['email' => $email],
          ],
          'subject' => $subject,
        ],
      ],

      'from' => [
        'email' => config('mail.from.address'),
        'name' => config('mail.from.name'),
      ],

      'content' => [
        [
          'type' => 'text/html',
          'value' => $htmlContent,
        ],
      ],
    ]);

    if (! $response->successful()) {
      logger('SendGrid Error:', $response->json());

      EmailLog::create([
        'campaign_id' => $campaign->id,
        'recipient_email' => $email,
        'status' => 'failed',
        'error' => $response->json()['errors'][0]['message'] ?? 'Unknown error',
      ]);
    } else {
      EmailLog::create([
        'campaign_id' => $campaign->id,
        'recipient_email' => $email,
        'status' => 'sent',
      ]);
    }
  }
}
