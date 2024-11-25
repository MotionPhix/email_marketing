<?php

namespace App\Models;

use App\Traits\BootUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
  use HasFactory, BootUuid;

  protected $fillable = ['name', 'content', 'description', 'design', 'user_id', 'type'];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function campaigns()
  {
    return $this->hasMany(Campaign::class);
  }

  public function scopeUserAndSystem($query, $userId)
  {
    return $query->where('user_id', $userId)->orWhereNull('user_id');
  }
}
