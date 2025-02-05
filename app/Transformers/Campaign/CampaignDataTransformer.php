<?php

namespace App\Transformers\Campaign;

use App\Models\Campaign;

class CampaignDataTransformer
{
  public static function toArray(Campaign $campaign): array
  {
    return [
      'id' => $campaign->id,
      'uuid' => $campaign->uuid,
      'title' => $campaign->title,
      'subject' => $campaign->subject,
      'description' => $campaign->description,
      'status' => $campaign->status,
      'step' => $campaign->step,

      // Schedule related fields
      'scheduled_at' => $campaign->scheduled_at?->toDateTimeString(),
      'end_date' => $campaign->end_date?->toDateTimeString(),
      'formatted_scheduled_at' => $campaign->formatted_scheduled_at,
      'formatted_end_date' => $campaign->formatted_end_date,
      'frequency' => $campaign->frequency,

      // Relationships
      'template' => $campaign->template ? [
        'id' => $campaign->template->id,
        'uuid' => $campaign->template->uuid,
        'name' => $campaign->template->name,
      ] : null,

      'audience' => $campaign->audience ? [
        'id' => $campaign->audience->id,
        'uuid' => $campaign->audience->uuid,
        'name' => $campaign->audience->name,
        'recipients' => $campaign->audience->recipients,
      ] : null,

      // Meta information
      'can_edit' => in_array($campaign->status, [
        Campaign::STATUS_DRAFT,
        Campaign::STATUS_SENT,
        Campaign::STATUS_FAILED
      ]),
      'can_schedule' => !in_array($campaign->status, [
        Campaign::STATUS_SENDING,
        Campaign::STATUS_SCHEDULED,
        Campaign::STATUS_FAILED
      ]),
      'can_send' => !in_array($campaign->status, [
        Campaign::STATUS_SENT,
        Campaign::STATUS_SENDING,
        Campaign::STATUS_SCHEDULED
      ]),

      // Timestamps
      'created_at' => $campaign->created_at?->toDateTimeString(),
      'updated_at' => $campaign->updated_at?->toDateTimeString(),
    ];
  }
}
