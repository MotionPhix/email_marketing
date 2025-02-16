<?php

namespace App\Actions\Fortify;

use App\Models\InvitedTeamMember;
use App\Models\User;
use App\Notifications\TeamInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
  use PasswordValidationRules;

  /**
   * Validate and create a newly registered user.
   *
   * @param array<string, string> $input
   */
  public function create(array $input): User
  {
    Validator::make($input, [
      'first_name' => ['required', 'string', 'max:255'],
      'last_name' => ['required', 'string', 'max:255'],
      'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
      'password' => $this->passwordRules(),
      'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',

      // Organization details
      'organization_name' => ['required', 'string', 'max:255'],
      'organization_size' => ['required', 'string'],
      'industry' => ['required', 'string'],
      'website' => ['nullable', 'url'],

      // Team members
      'team_members' => ['nullable', 'array'],
      'team_members.*.email' => ['required', 'email', 'distinct'],
      'team_members.*.role' => ['required', 'string', 'in:admin,editor,member'],
    ], [
      'team_members.*.email.required' => 'Enter the member\'s email address',
      'team_members.*.email.email' => 'Enter a valid email address',
      'team_members.*.email.distinct' => 'You have already entered this email',
    ])->validate();

    return DB::transaction(function () use ($input) {
      $user = User::create([
        'first_name' => $input['first_name'],
        'last_name' => $input['last_name'],
        'email' => $input['email'],
        'password' => Hash::make($input['password']),
        'company_name' => $input['organization_name'],
        'company_size' => $input['organization_size'],
        'industry' => $input['industry'],
        'website' => $input['website'] ?? null,
        'registration_status' => 'incomplete',
        'completed_registration_steps' => [
          User::REGISTRATION_STEP_ACCOUNT,
          User::REGISTRATION_STEP_ORGANIZATION
        ],
        'trial_ends_at' => now()->addDays(14),
      ]);

      // Create team
      $team = $user->ownedTeams()->create([
        'name' => $input['organization_name'],
        'personal_team' => true,
      ]);

      $user->current_team_id = $team->id;
      $user->save();

      // Process team invitations
      if (!empty($input['team_members'])) {
        foreach ($input['team_members'] as $member) {
          $invitation = InvitedTeamMember::invite([
            'user_id' => $user->id,
            'team_id' => $team->id,
            'email' => $member['email'],
            'role' => $member['role'],
          ]);

          // Send invitation email
          $invitation->notify(new TeamInvitation($user, $team));

          // Mark team setup step as completed
          $user->completeRegistrationStep(User::REGISTRATION_STEP_TEAM);
        }
      }

      DB::commit();

      return $user;
    });
  }
}
