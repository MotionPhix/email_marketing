<?php

namespace App\Policies;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class EmailTemplatePolicy
{
  use HandlesAuthorization;

  /**
   * Determine whether the user can view the model.
   */
  public function view(User $user, EmailTemplate $template): bool
  {
    return $user->currentTeam->id === $template->team_id;
  }

  /**
   * Determine whether the user can create models.
   */
  public function create(User $user): bool
  {
    return true;
  }

  /**
   * Determine whether the user can update the model.
   */
  public function update(User $user, EmailTemplate $template): bool
  {
    return $user->currentTeam->id === $template->team_id;
  }

  /**
   * Determine whether the user can delete the model.
   */
  public function delete(User $user, EmailTemplate $template): bool
  {
    return $user->currentTeam->id === $template->team_id;
  }
}
