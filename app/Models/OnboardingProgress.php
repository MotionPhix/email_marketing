<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingProgress extends Model
{
  protected $fillable = [
    'user_id',
    'completed_steps',
    'form_data',
    'is_completed',
    'completed_at',
  ];

  protected $casts = [
    'completed_steps' => 'array',
    'form_data' => 'array',
    'is_completed' => 'boolean',
    'completed_at' => 'datetime',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
