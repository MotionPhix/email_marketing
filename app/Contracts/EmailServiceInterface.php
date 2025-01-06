<?php

namespace App\Contracts;

use App\Models\Campaign;
use App\Models\EmailLog;
use App\Models\Recipient;

interface EmailServiceInterface
{
  public function send(Campaign $campaign, Recipient $recipient): bool;

  public function track(EmailLog $log): void;
}
