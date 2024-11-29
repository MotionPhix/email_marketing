<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Services\CampaignEmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScheduleCampaignJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  /**
   * Create a new job instance.
   */
  public function __construct(
    public Campaign $campaign,
    protected CampaignEmailService $campaignEmailService
  ) {}

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    $recipients = $this->campaign->audience->recipients;
    $this->campaignEmailService->sendEmails($this->campaign, $recipients);
  }
}
