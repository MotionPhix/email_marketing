<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailProvider extends Model
{
  protected $fillable = [
    'name',
    'slug',
    'description',
    'required_fields',
    'is_enabled'
  ];

  protected $casts = [
    'required_fields' => 'array',
    'is_enabled' => 'boolean'
  ];

  public function userProviders(): HasMany
  {
    return $this->hasMany(UserEmailProvider::class);
  }
}
