<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationData extends Model
{
  protected $fillable = [
    'user_id',
    'step',
    'data',
    'validation_errors',
    'completed_at'
  ];

  protected $casts = [
    'step' => 'integer',
    'data' => 'array',
    'validation_errors' => 'array',
    'completed_at' => 'datetime'
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
