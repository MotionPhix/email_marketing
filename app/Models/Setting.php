<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
  protected $fillable = [
    'user_id',
    'preferences',
    'notification_settings',
    'email_settings',
    'branding_settings',
  ];

  protected $casts = [
    'preferences' => 'array',
    'notification_settings' => 'array',
    'email_settings' => 'array',
    'branding_settings' => 'array',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
