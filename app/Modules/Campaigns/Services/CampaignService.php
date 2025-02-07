<?php

namespace App\Modules\Campaigns\Services;

use App\Modules\Campaigns\Models\Campaign;
use App\Modules\Campaigns\Models\CampaignEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CampaignService
{
  public function create(array $data): Campaign
  {
    return DB::transaction(function () use ($data) {
      $campaign = Campaign::create([
        'user_id' => auth()->id(),
        'name' => $data['name'],
        'subject' => $data['subject'],
        'content' => $data['content'],
        'template_data' => $data['template_data'] ?? null,
        'from_name' => $data['from_name'] ?? null,
        'from_email' => $data['from_email'] ?? null,
        'reply_to' => $data['reply_to'] ?? null,
        'scheduled_at' => $data['scheduled_at'] ?? null,
        'status' => $data['scheduled_at'] ? Campaign::STATUS_SCHEDULED : Campaign::STATUS_DRAFT,
      ]);

      if (!empty($data['list_ids'])) {
        $campaign->lists()->attach($data['list_ids']);
        $campaign->total_recipients = $this->calculateTotalRecipients($campaign);
        $campaign->save();
      }

      return $campaign;
    });
  }

  public function update(Campaign $campaign, array $data): Campaign
  {
    return DB::transaction(function () use ($campaign, $data) {
      $campaign->update([
        'name' => $data['name'],
        'subject' => $data['subject'],
        'content' => $data['content'],
        'template_data' => $data['template_data'] ?? $campaign->template_data,
        'from_name' => $data['from_name'] ?? $campaign->from_name,
        'from_email' => $data['from_email'] ?? $campaign->from_email,
        'reply_to' => $data['reply_to'] ?? $campaign->reply_to,
        'scheduled_at' => $data['scheduled_at'] ?? $campaign->scheduled_at,
        'status' => $data['scheduled_at'] ? Campaign::STATUS_SCHEDULED : Campaign::STATUS_DRAFT,
      ]);

      if (isset($data['list_ids'])) {
        $campaign->lists()->sync($data['list_ids']);
        $campaign->total_recipients = $this->calculateTotalRecipients($campaign);
        $campaign->save();
      }

      return $campaign->fresh(['lists']);
    });
  }

  public function schedule(Campaign $campaign, Carbon $scheduledAt): Campaign
  {
    if (!$campaign->canBeEdited()) {
      throw new \Exception('Campaign cannot be scheduled');
    }

    $campaign->update([
      'scheduled_at' => $scheduledAt,
      'status' => Campaign::STATUS_SCHEDULED,
    ]);

    return $campaign;
  }

  public function cancel(Campaign $campaign): Campaign
  {
    if (!$campaign->isScheduled()) {
      throw new \Exception('Only scheduled campaigns can be cancelled');
    }

    $campaign->update([
      'scheduled_at' => null,
      'status' => Campaign::STATUS_DRAFT,
    ]);

    return $campaign;
  }

  public function delete(Campaign $campaign): bool
  {
    if (!$campaign->canBeEdited()) {
      throw new \Exception('Campaign cannot be deleted');
    }

    return DB::transaction(function () use ($campaign) {
      $campaign->lists()->detach();
      return $campaign->delete();
    });
  }

  public function recordEvent(Campaign $campaign, string $email, string $type, array $metadata = []): void
  {
    CampaignEvent::create([
      'campaign_id' => $campaign->id,
      'email' => $email,
      'type' => $type,
      'metadata' => $metadata,
    ]);

    $campaign->increment($this->getCountColumnForEventType($type));
  }

  protected function calculateTotalRecipients(Campaign $campaign): int
  {
    return $campaign->lists()
      ->join('list_subscribers', 'mailing_lists.id', '=', 'list_subscribers.list_id')
      ->where('list_subscribers.status', 'subscribed')
      ->count();
  }

  protected function getCountColumnForEventType(string $type): string
  {
    return match ($type) {
      CampaignEvent::TYPE_SENT => 'sent_count',
      CampaignEvent::TYPE_OPENED => 'opened_count',
      CampaignEvent::TYPE_CLICKED => 'clicked_count',
      CampaignEvent::TYPE_BOUNCED => 'bounced_count',
      CampaignEvent::TYPE_COMPLAINED => 'complained_count',
      default => throw new \Exception('Invalid event type'),
    };
  }

  public function sendTest(Campaign $campaign, string $email): void
  {
    $subscriber = [
      'email' => $email,
      'fields' => [
        'email' => $email,
        // Add any test data you want to use for personalization
        'first_name' => 'Test',
        'last_name' => 'User',
      ],
    ];

    // You'll need to implement this mail class
    Mail::to($email)->send(new CampaignEmail($campaign, $subscriber));
  }
}
