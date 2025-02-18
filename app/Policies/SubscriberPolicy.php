<?php

namespace App\Policies;

use App\Models\Subscriber;
use App\Models\User;

class SubscriberPolicy
{
  public function view(User $user, Subscriber $subscriber): bool
  {
    return $user->currentTeam->id === $subscriber->team_id;
  }

  public function update(User $user, Subscriber $subscriber): bool
  {
    return $user->currentTeam->id === $subscriber->team_id;
  }

  public function delete(User $user, Subscriber $subscriber): bool
  {
    return $user->currentTeam->id === $subscriber->team_id;
  }
}
