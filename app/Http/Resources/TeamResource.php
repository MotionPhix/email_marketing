<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
  public function toArray($request): array
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'owner' => new UserResource($this->owner),
      'members' => UserResource::collection($this->whenLoaded('users')),
      'invitations' => TeamInvitationResource::collection($this->whenLoaded('invitations')),
      'personal_team' => $this->personal_team,
      'settings' => $this->settings,
      'created_at' => $this->created_at
    ];
  }
}
