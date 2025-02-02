<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('campaign.stats.{userId}', function ($user, $userId) {
  \Illuminate\Support\Facades\Log::info($user);
  return (int) $user->id === (int) $userId;
});
