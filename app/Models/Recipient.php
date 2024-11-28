<?php

namespace App\Models;

use App\Traits\BootUuid;
use Database\Factories\RecipientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
  use HasFactory, BootUuid;

  protected $fillable = ['audience_id', 'email', 'name', 'user_id', 'gender'];

  protected static function newFactory()
  {
    return RecipientFactory::new();
  }

  public function audiences()
  {
    return $this->belongsToMany(Audience::class, 'audience_recipient')
      ->withTimestamps();
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
