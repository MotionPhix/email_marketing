<?php

namespace App\Console\Commands;

use App\Services\Subscription\SubscriptionRenewalService;
use Illuminate\Console\Command;

class ProcessSubscriptionRenewals extends Command
{
  protected $signature = 'subscriptions:process-renewals';
  protected $description = 'Process subscription renewals and send notifications';

  public function handle(SubscriptionRenewalService $renewalService)
  {
    $this->info('Processing subscription renewals...');

    try {
      $renewalService->processRenewals();
      $this->info('Successfully processed renewals.');
    } catch (\Exception $e) {
      $this->error('Error processing renewals: ' . $e->getMessage());
    }
  }
}
