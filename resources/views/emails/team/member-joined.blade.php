@component('mail::message')
  # New Team Member Joined

  Hello {{ $notifiable->name }},

  **{{ $newMember->name }}** ({{ $newMember->email }}) has joined your team **{{ $team->name }}**.

  @component('mail::button', ['url' => route('team.members.show', $newMember->id)])
    View Team Member
  @endcomponent

  Thanks,<br>
  {{ config('app.name') }}
@endcomponent
