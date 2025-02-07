<?php

namespace App\Modules\Settings\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
  protected $fillable = [
    'user_id',
    'key',
    'value',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
