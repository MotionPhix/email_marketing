<?php

namespace App\Repositories;

use App\Models\Campaign;

class CampaignRepository
{
  public function create(array $data)
  {
    return Campaign::create($data);
  }

  public function getPendingCampaigns()
  {
    return Campaign::where('status', Campaign::STATUS_SCHEDULED)
      ->where('scheduled_at', '<=', now())
      ->get();
  }
}
