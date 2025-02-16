<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BrandingResource extends JsonResource
{
  public function toArray($request): array
  {
    return [
      'id' => $this->id,
      'company_name' => $this->company_name,
      'logo_url' => $this->logo_path ? Storage::url($this->logo_path) : null,
      'primary_color' => $this->primary_color,
      'accent_color' => $this->accent_color,
      'email_header' => $this->email_header,
      'email_footer' => $this->email_footer,
      'custom_css' => $this->custom_css,
      'updated_at' => $this->updated_at
    ];
  }
}
