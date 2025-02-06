<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\User;
use App\Services\CampaignEmailService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
    public Carbon $scheduledDate,
    public string $frequency,
    public ?Carbon $endDate = null,
    public int $userId
  ) {}

  /**
   * Execute the job.
   */
  public function handle(CampaignEmailService $campaignEmailService): void
  {
    if ($this->campaign->audience) {
      $user = User::findOrFail($this->userId);
      $recipients = $this->campaign->audience->recipients;
      $campaignEmailService->sendEmails($this->campaign, $recipients, $user);
    }
  }
}
