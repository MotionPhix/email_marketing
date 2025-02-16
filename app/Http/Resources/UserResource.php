<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
  public function toArray($request): array
  {
    return [
      'id' => $this->id,
      'first_name' => $this->first_name,
      'last_name' => $this->last_name,
      'email' => $this->email,
      'company_name' => $this->company_name,
      'phone' => $this->phone,
      'timezone' => $this->timezone,
      'language' => $this->language,
      'profile_photo_url' => $this->profile_photo_url,
      'email_signature' => $this->email_signature,
      'default_sender_email' => $this->default_sender_email,
      'default_sender_name' => $this->default_sender_name,
      'account_status' => $this->account_status,
      'industry' => $this->industry,
      'company_size' => $this->company_size,
      'website' => $this->website,
      'is_trial' => $this->is_trial,
      'subscription_status' => $this->subscription_status,
      'registration_progress' => $this->registration_progress,
      'email_verified_at' => $this->email_verified_at,
      'last_login_at' => $this->last_login_at,
      'trial_ends_at' => $this->trial_ends_at,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,

      // Conditionally loaded relationships
      'current_team' => new TeamResource($this->whenLoaded('currentTeam')),
      'teams' => TeamResource::collection($this->whenLoaded('teams')),
      'owned_teams' => TeamResource::collection($this->whenLoaded('ownedTeams')),
      'branding' => new BrandingResource($this->whenLoaded('branding')),
      'email_quota' => new QuotaResource($this->whenLoaded('emailQuota')),
    ];
  }
}
