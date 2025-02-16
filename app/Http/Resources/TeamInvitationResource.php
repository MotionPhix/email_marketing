<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamInvitationResource extends JsonResource
{
  public function toArray($request): array
  {
    return [
      'id' => $this->id,
      'team_id' => $this->team_id,
      'email' => $this->email,
      'role' => $this->role,
      'token' => $this->invitation_token,
      'status' => $this->status_label,
      'invitation_url' => $this->invitation_url,
      'is_expired' => $this->is_expired,
      'can_resend' => $this->can_resend,
      'days_until_expiry' => $this->days_until_expiry,
      'invited_at' => $this->invited_at,
      'accepted_at' => $this->accepted_at,
      'expires_at' => $this->expires_at,
      'last_sent_at' => $this->last_sent_at,
      'send_count' => $this->send_count,

      // Include the team and inviter details when loaded
      'team' => new TeamResource($this->whenLoaded('team')),
      'inviter' => new UserResource($this->whenLoaded('inviter')),
    ];
  }
}
