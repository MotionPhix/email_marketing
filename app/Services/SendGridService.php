<?php

namespace App\Services;

use SendGrid;

class SendGridService
{
  protected $sendGrid;

  public function __construct()
  {
    $this->sendGrid = new SendGrid(env('SENDGRID_API_KEY'));
  }

  public function getCampaignStats(string $campaignId, string $startDate, string $endDate)
  {
    try {
      $response = $this->sendGrid->client->messages()->search()->get([
        'query' => "unique_args.campaign_id=$campaignId",
        'start_date' => $startDate,
        'end_date' => $endDate,
      ]);

      if ($response->statusCode() !== 200) {
        throw new \Exception('Failed to fetch stats');
      }

      return json_decode($response->body(), true);
    } catch (\Exception $e) {
      \Log::error('Error fetching SendGrid stats: ' . $e->getMessage());
      return [];
    }
  }
}
