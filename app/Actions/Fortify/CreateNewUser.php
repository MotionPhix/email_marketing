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
    $this->validateInput($input);

    return DB::transaction(function () use ($input) {
      $user = $this->createUser($input);
      $this->createUserSettings($user, $input);
      $team = $this->createTeam($user, $input);

      if (!empty($input['team_members'])) {
        $this->processTeamInvitations($user, $team, $input['team_members']);
      }

      return $user;
    });
  }

  protected function validateInput(array $input): void
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
  }

  protected function createUser(array $input): User
  {
    return User::create([
      'first_name' => $input['first_name'],
      'last_name' => $input['last_name'],
      'email' => $input['email'],
      'password' => Hash::make($input['password']),
      'registration_status' => 'incomplete',
      'completed_registration_steps' => [
        User::REGISTRATION_STEP_ACCOUNT,
        User::REGISTRATION_STEP_ORGANIZATION
      ],
    ]);
  }

  protected function createUserSettings(User $user, array $input): void
  {
    $user->settings()->create([
      'preferences' => [
        'language' => 'en',
        'timezone' => 'UTC',
      ],
      'notification_settings' => [
        'email_notifications' => true,
        'in_app_notifications' => true,
      ],
      'email_settings' => [
        'from_name' => null,
        'reply_to' => null,
      ],
      'sender_settings' => [
        'default_sender_name' => null,
        'default_sender_email' => null,
        'email_verified' => false,
        'verification_token' => null,
      ],
      'marketing_settings' => [
        'email_updates' => true,
        'product_news' => true,
        'marketing_communications' => true,
      ],
      'company_settings' => [
        'company_name' => $input['organization_name'],
        'industry' => $input['industry'],
        'company_size' => $input['organization_size'],
        'website' => $input['website'] ?? null,
        'phone' => $input['phone'] ?? null,
        'role' => null,
      ],
      'branding_settings' => [
        'logo_url' => null,
        'primary_color' => '#4F46E5',
        'accent_color' => '#818CF8',
      ],
      'subscription_settings' => [
        'plan' => 'free',
        'email_quota' => 100,
        'features' => [
          'custom_domain' => false,
          'api_access' => false,
          'advanced_analytics' => false,
        ],
        'trial_ends_at' => now()->addDays(14),
      ],
    ]);
  }

  protected function createTeam(User $user, array $input): mixed
  {
    $team = $user->ownedTeams()->create([
      'name' => $input['organization_name'],
      'personal_team' => true,
    ]);

    $user->current_team_id = $team->id;
    $user->save();

    return $team;
  }

  protected function processTeamInvitations(User $user, $team, array $members): void
  {
    foreach ($members as $member) {
      $invitation = InvitedTeamMember::invite([
        'user_id' => $user->id,
        'team_id' => $team->id,
        'email' => $member['email'],
        'role' => $member['role'],
      ]);

      // Queue the invitation email
      $invitation->notify(new TeamInvitation($user, $team));
    }

    // Mark team setup step as completed
    $user->completeRegistrationStep(User::REGISTRATION_STEP_TEAM);
  }
}
