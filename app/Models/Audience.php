<?php

namespace App\Models;

use App\Traits\BootUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audience extends Model
{
  use HasFactory, BootUuid;

  protected $fillable = ['name', 'description', 'user_id'];

  public function recipients()
  {
    return $this->belongsToMany(
      Recipient::class,
      'audience_recipient',
      'audience_id',
      'recipient_id'
    );
  }


  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function campaigns()
  {
    return $this->hasMany(Campaign::class);
  }
}
