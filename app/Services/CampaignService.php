<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignEvent;
use App\Models\Subscriber;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use SendGrid\Mail\Mail;
use SendGrid\Mail\Personalization;

class CampaignService
{
  public function __construct(
    protected EmailPersonalizationService $emailPersonalization,
    protected SendGridService $sendGrid
  ) {}

  public function create(array $data): Campaign
  {
    return DB::transaction(function () use ($data) {
      $campaign = Campaign::create([
        'user_id' => auth()->user()->id,
        'team_id' => auth()->user()->currentTeam->id,
        'name' => $data['name'],
        'subject' => $data['subject'],
        'from_name' => $data['from_name'],
        'from_email' => $data['from_email'],
        'reply_to' => $data['reply_to'],
        'content' => $data['content'] ?? '',
        'template_id' => $data['template_id'],
        'scheduled_at' => $data['scheduled_at'] ?? null,
        'recipients' => $data['recipients'],
        'settings' => $data['settings'] ?? [],
        'current_step' => $data['current_step'] ?? 1,
        'status' => 'draft'
      ]);

      return $campaign;
    });
  }

  public function update(Campaign $campaign, array $data): Campaign
  {
    if (!in_array($campaign->status, ['draft', 'scheduled'])) {
      throw new \Exception('Only draft or scheduled campaigns can be updated.');
    }

    return DB::transaction(function () use ($campaign, $data) {
      $campaign->update([
        'name' => $data['name'],
        'subject' => $data['subject'],
        'from_name' => $data['from_name'],
        'from_email' => $data['from_email'],
        'reply_to' => $data['reply_to'],
        'content' => $data['content'] ?? '',
        'template_id' => $data['template_id'],
        'scheduled_at' => $data['scheduled_at'] ?? null,
        'recipients' => $data['recipients'],
        'settings' => array_merge($campaign->settings ?? [], $data['settings'] ?? []),
        'current_step' => $data['current_step'] ?? $campaign->current_step,
      ]);

      return $campaign->fresh(['template']);
    });
  }

  public function schedule(Campaign $campaign, string $scheduledAt, string $timezone): Campaign
  {
    if ($campaign->status !== 'draft') {
      throw new \Exception('Only draft campaigns can be scheduled.');
    }

    $campaign->update([
      'status' => 'scheduled',
      'scheduled_at' => Carbon::parse($scheduledAt, $timezone)->tz('UTC'),
    ]);

    return $campaign->fresh();
  }

  public function sendCampaign(Campaign $campaign)
  {
    if (!in_array($campaign->status, ['draft', 'scheduled'])) {
      throw new \Exception('Only draft or scheduled campaigns can be sent.');
    }

    $campaign->update([
      'status' => 'sending',
      'sent_at' => now(),
    ]);

    $email = new Mail();
    $email->setFrom($campaign->from_email, $campaign->from_name);
    $email->setSubject($campaign->subject);

    if ($campaign->reply_to) {
      $email->setReplyTo($campaign->reply_to);
    }

    // Add tracking data
    $trackingData = [
      'campaign_id' => $campaign->id,
      'tags' => [
        'team_' . $campaign->team_id,
        'template_' . ($campaign->template_id ?? 'custom'),
        $campaign->status
      ]
    ];

    // Get subscribers
    $subscribers = Subscriber::whereIn('list_id', $campaign->recipients)
      ->get();

    foreach ($subscribers as $subscriber) {
      $personalization = new Personalization();
      $personalization->addTo($subscriber->email);

      // Add custom variables for tracking
      $personalization->addCustomArg('subscriber_id', (string)$subscriber->id);

      // Add template variables
      $personalization->addDynamicTemplateData('subscriber', [
        'first_name' => $subscriber->first_name,
        'last_name' => $subscriber->last_name,
        'email' => $subscriber->email,
        'unsubscribe_url' => route('subscribers.unsubscribe', [
          'subscriber' => $subscriber->id,
          'campaign' => $campaign->id
        ])
      ]);

      $personalization->addDynamicTemplateData('campaign', [
        'name' => $campaign->name,
        'subject' => $campaign->subject,
        'web_view_url' => route('campaigns.web-view', $campaign)
      ]);

      $email->addPersonalization($personalization);
    }

    // Process template content
    $content = $this->emailPersonalization->parseTemplate(
      $campaign->content,
      [] // Base variables, personalization handled by SendGrid
    );

    $email->addContent('text/html', $content);

    // Send via SendGrid with tracking
    return $this->sendGrid->send($email, $trackingData);
  }

  public function duplicate(Campaign $campaign): Campaign
  {
    $newCampaign = $campaign->replicate();
    $newCampaign->name = "Copy of {$campaign->name}";
    $newCampaign->status = 'draft';
    $newCampaign->scheduled_at = null;
    $newCampaign->sent_at = null;
    $newCampaign->save();

    return $newCampaign->fresh(['template']);
  }

  public function delete(Campaign $campaign): void
  {
    if (!in_array($campaign->status, ['draft', 'scheduled'])) {
      throw new \Exception('Only draft or scheduled campaigns can be deleted.');
    }

    $campaign->delete();
  }

  public function getDetailedStats(Campaign $campaign): array
  {
    $events = CampaignEvent::where('campaign_id', $campaign->id)
      ->select('type', DB::raw('count(*) as count'))
      ->groupBy('type')
      ->get()
      ->pluck('count', 'type')
      ->toArray();

    $total = $campaign->events()->count() ?: 1; // Prevent division by zero

    return [
      'delivered' => [
        'count' => $events['delivered'] ?? 0,
        'rate' => ($events['delivered'] ?? 0) / $total * 100
      ],
      'opened' => [
        'count' => $events['opened'] ?? 0,
        'rate' => ($events['opened'] ?? 0) / $total * 100
      ],
      'clicked' => [
        'count' => $events['clicked'] ?? 0,
        'rate' => ($events['clicked'] ?? 0) / $total * 100
      ],
      'bounced' => [
        'count' => $events['bounced'] ?? 0,
        'rate' => ($events['bounced'] ?? 0) / $total * 100
      ],
      'complaints' => [
        'count' => $events['complaint'] ?? 0,
        'rate' => ($events['complaint'] ?? 0) / $total * 100
      ],
    ];
  }

  public function getOpenRateOverTime(Campaign $campaign): array
  {
    return $this->getMetricOverTime($campaign, 'opened');
  }

  public function getClickRateOverTime(Campaign $campaign): array
  {
    return $this->getMetricOverTime($campaign, 'clicked');
  }

  public function getEngagementMetrics(Campaign $campaign): array
  {
    $events = CampaignEvent::where('campaign_id', $campaign->id)
      ->whereIn('type', ['opened', 'clicked'])
      ->select(
        'subscriber_id',
        DB::raw('COUNT(DISTINCT CASE WHEN type = "opened" THEN id END) as opens'),
        DB::raw('COUNT(DISTINCT CASE WHEN type = "clicked" THEN id END) as clicks')
      )
      ->groupBy('subscriber_id')
      ->get();

    return [
      'highly_engaged' => $events->filter(fn($e) => $e->opens > 2 && $e->clicks > 0)->count(),
      'moderately_engaged' => $events->filter(fn($e) => $e->opens > 0 && $e->clicks > 0)->count(),
      'low_engaged' => $events->filter(fn($e) => $e->opens > 0 && $e->clicks === 0)->count(),
      'not_engaged' => $events->filter(fn($e) => $e->opens === 0 && $e->clicks === 0)->count(),
    ];
  }

  protected function getMetricOverTime(Campaign $campaign, string $type): array
  {
    return CampaignEvent::where('campaign_id', $campaign->id)
      ->where('type', $type)
      ->select(
        DB::raw('DATE(created_at) as date'),
        DB::raw('COUNT(*) as count')
      )
      ->groupBy('date')
      ->orderBy('date')
      ->get()
      ->map(fn($event) => [
        'date' => $event->date,
        'count' => $event->count,
      ])
      ->toArray();
  }
}
