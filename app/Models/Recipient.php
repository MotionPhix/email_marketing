<?php

namespace App\Models;

use App\Traits\BootUuid;
use Database\Factories\RecipientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipient extends Model
{
  use HasFactory, BootUuid, SoftDeletes;

  protected $fillable = ['audience_id', 'status', 'email', 'name', 'user_id', 'gender'];

  protected static function newFactory()
  {
    return RecipientFactory::new();
  }

  public function audiences()
  {
    return $this->belongsToMany(
      Audience::class,
      'audience_recipient',
      'recipient_id',
      'audience_id'
    );
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function unsubscribes()
  {
    return $this->hasMany(CampaignUnsubscribe::class);
  }

  public function unsubscribedFromCampaign(Campaign $campaign)
  {
    return $this->unsubscribes()->where('campaign_id', $campaign->id)->exists();
  }
}
