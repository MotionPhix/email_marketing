<?php

namespace App\Services;

use App\Contracts\EmailServiceInterface;
use App\Models\Campaign;
use App\Repositories\CampaignRepository;

class CampaignService
{
  private $repository;
  private $emailService;

  public function __construct(
    CampaignRepository    $repository,
    EmailServiceInterface $emailService
  )
  {
    $this->repository = $repository;
    $this->emailService = $emailService;
  }

  public function schedule(Campaign $campaign)
  {
    // Implementation
  }

  public function send(Campaign $campaign)
  {
    // Implementation
  }
}
