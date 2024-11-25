<?php

namespace App\Models;

use App\Traits\BootUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
  use HasFactory, BootUuid;

  protected $fillable = ['campaign_id', 'recipient_email', 'status'];

  public function campaign()
  {
    return $this->belongsTo(Campaign::class);
  }
}
