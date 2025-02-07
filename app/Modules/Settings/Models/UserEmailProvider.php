<?php

namespace App\Modules\Settings\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEmailProvider extends Model
{
  protected $fillable = [
    'user_id',
    'email_provider_id',
    'credentials',
    'is_active',
    'last_used_at'
  ];

  protected $casts = [
    'credentials' => 'encrypted:array',
    'is_active' => 'boolean',
    'last_used_at' => 'datetime'
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function provider(): BelongsTo
  {
    return $this->belongsTo(EmailProvider::class, 'email_provider_id');
  }
}
