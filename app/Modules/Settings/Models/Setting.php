<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
  protected $fillable = [
    'key',
    'value',
    'type',
    'group',
    'label',
    'description',
    'options',
    'is_public',
    'is_system',
  ];

  protected $casts = [
    'options' => 'array',
    'is_public' => 'boolean',
    'is_system' => 'boolean',
  ];

  public function getTypedValueAttribute(): mixed
  {
    return match($this->type) {
      'boolean' => (bool) $this->value,
      'integer' => (int) $this->value,
      'float' => (float) $this->value,
      'json' => json_decode($this->value, true),
      'array' => explode(',', $this->value),
      default => $this->value,
    };
  }
}
