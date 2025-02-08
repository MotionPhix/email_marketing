<?php

namespace App\Modules\Lists\Models;

use App\Models\Recipient;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MailingList extends Model
{
  use SoftDeletes, HasUuids;

  protected $fillable = [
    'user_id',
    'name',
    'description',
    'is_default',
    'metadata'
  ];

  protected $casts = [
    'is_default' => 'boolean',
    'metadata' => 'array'
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function recipients(): BelongsToMany
  {
    return $this->belongsToMany(Recipient::class, 'list_recipients')
      ->withTimestamps()
      ->withPivot([
        'status',
        'subscribed_at',
        'unsubscribed_at'
      ]);
  }

  public function activeRecipients(): BelongsToMany
  {
    return $this->recipients()
      ->wherePivot('status', 'subscribed');
  }

  public function campaigns(): BelongsToMany
  {
    return $this->belongsToMany(Campaign::class, 'campaign_lists')
      ->withTimestamps();
  }

  public function getActiveRecipientsCount(): int
  {
    return $this->activeRecipients()->count();
  }

  public function subscribe(Recipient $recipient): void
  {
    $this->recipients()->attach($recipient->id, [
      'status' => 'subscribed',
      'subscribed_at' => now()
    ]);
  }

  public function unsubscribe(Recipient $recipient, ?string $reason = null): void
  {
    $this->recipients()->updateExistingPivot($recipient->id, [
      'status' => 'unsubscribed',
      'unsubscribed_at' => now(),
      'metadata' => [
        'unsubscribe_reason' => $reason
      ]
    ]);
  }
}
