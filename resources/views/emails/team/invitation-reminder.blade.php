@component('mail::message')
  # Reminder: You Have a Pending Team Invitation

  Hi there,

  This is a friendly reminder that {{ $inviter->name }} has invited you to join their team "{{ $team->name }}" on {{ config('app.name') }}.

  This invitation will expire in {{ $daysLeft }} {{ Str::plural('day', $daysLeft) }}.

  @component('mail::button', ['url' => $invitation->invitation_url])
    Accept Invitation
  @endcomponent

  If you didn't expect this invitation, you can safely ignore this email.

  Thanks,<br>
  {{ config('app.name') }}
@endcomponent
