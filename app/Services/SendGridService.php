<?php

namespace App\Services;

use App\Models\Campaign;
use SendGrid;

class SendGridService
{
  protected $sendGrid;

  public function __construct()
  {
    $this->sendGrid = new SendGrid(config('services.sendgrid.api_key'));
  }

  /**
   * Get email stats for a campaign using SendGrid's stats endpoint.
   *
   * @param string $startDate - Start date in YYYY-MM-DD format.
   * @param string $endDate - End date in YYYY-MM-DD format.
   * @param string|null $campaignId - Optional campaign identifier.
   * @return array
   */
  public function getEmailStats(string $startDate, string $endDate, string $campaignId = null): array
  {
    try {
      // Optional filters based on campaign_id
      $queryParams = [
        'start_date' => $startDate,
        'end_date' => $endDate,
      ];

      if ($campaignId) {
        $campaign = Campaign::where('uuid', $campaignId)->first();
        $queryParams['categories'] = $campaign->name;
      }

      $response = $this->sendGrid->client->stats()->get(null, $queryParams);

      if ($response->statusCode() !== 200) {
        throw new \Exception('Failed to fetch stats. Status code: ' . $response->statusCode());
      }

      return json_decode($response->body(), true);

    } catch (\Exception $e) {
      \Log::error('Error fetching SendGrid stats: ' . $e->getMessage());
      return [];
    }
  }

  /**
   * Extract detailed message activity for a specific campaign.
   *
   * @param string $campaignId
   * @return array
   */
  public function getMessageActivity(string $campaignId): array
  {
    try {
//      $response = $this->sendGrid->client->messages()->get([
//        'query' => "unique_args.campaign_id=$campaignId",
//      ]);

      // Filter by unique argument for the campaign
      $queryParams = [
        'query' => 'unique_args.campaign_id=' . $campaignId,
      ];

      $response = $this->sendGrid->client->messages()->get(null, $queryParams);

      if ($response->statusCode() !== 200) {
        throw new \Exception('Failed to fetch message activity. Status code: ' . $response->statusCode());
      }

      return json_decode($response->body(), true);
    } catch (\Exception $e) {
      \Log::error('Error fetching SendGrid message activity: ' . $e->getMessage());
      return [];
    }
  }
}
