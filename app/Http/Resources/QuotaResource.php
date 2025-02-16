<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuotaResource extends JsonResource
{
  public function toArray($request): array
  {
    return [
      'id' => $this->id,
      'monthly_limit' => $this->monthly_limit,
      'monthly_used' => $this->monthly_used,
      'daily_limit' => $this->daily_limit,
      'daily_used' => $this->daily_used,
      'monthly_remaining' => $this->monthly_limit - $this->monthly_used,
      'daily_remaining' => $this->daily_limit - $this->daily_used,
      'last_reset_at' => $this->last_reset_at
    ];
  }
}
