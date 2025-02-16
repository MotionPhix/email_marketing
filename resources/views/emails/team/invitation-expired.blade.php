@component('mail::message')
  # Team Invitation Expired

  Hello {{ $notifiable->name }},

  The invitation you sent to **{{ $invitation->email }}** to join **{{ $invitation->team->name }}** has expired.

  The invitation was sent on {{ $invitation->invited_at->format('F j, Y') }} and expired on {{ $invitation->expires_at->format('F j, Y') }}.

  @component('mail::button', ['url' => route('team.invitations.index')])
    Send New Invitation
  @endcomponent

  Thanks,<br>
  {{ config('app.name') }}
@endcomponent
