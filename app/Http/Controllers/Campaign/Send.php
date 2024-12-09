<?php

namespace App\Http\Controllers\Campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\EmailLog;
use App\Models\User;
use App\Services\CampaignEmailService;
use App\Services\TemplateRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Send extends Controller
{
  public function __construct(protected CampaignEmailService $emailService) {}

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
    }

    $this->sendCampaign($campaign, $request->user());

    return back();
  }

  /**
   * Send the campaign emails.
   */
  private function sendCampaign(Campaign $campaign, User $user)
  {
    $recipients = $campaign->audience->recipients;
    $this->emailService->sendEmails($campaign, $recipients, $user);
  }
}
