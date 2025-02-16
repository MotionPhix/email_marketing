<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Team Invitation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px;">
<div style="max-width: 600px; margin: 0 auto; background: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
  <div style="text-align: center; margin-bottom: 30px;">
    <img src="{{ asset('images/logo.png') }}" alt="Logo" style="max-width: 150px;">
  </div>

  <h1 style="color: #1a1a1a; font-size: 24px; margin-bottom: 20px; text-align: center;">
    You've Been Invited to Join {{ $team->name }}
  </h1>

  <p style="color: #4a4a4a; margin-bottom: 20px;">
    Hi there,
  </p>

  <p style="color: #4a4a4a; margin-bottom: 20px;">
    {{ $inviter->name }} has invited you to join their team on {{ config('app.name') }}.
    You'll be joining as a {{ $notifiable->role }}.
  </p>

  <div style="text-align: center; margin: 30px 0;">
    <a href="{{ $acceptUrl }}" style="display: inline-block; background-color: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">
      Accept Invitation
    </a>
  </div>

  <p style="color: #4a4a4a; margin-bottom: 20px;">
    If you're having trouble clicking the button, copy and paste this URL into your browser:
    <br>
    <span style="color: #6b7280;">{{ $acceptUrl }}</span>
  </p>

  <p style="color: #4a4a4a; margin-bottom: 10px;">
    This invitation will expire in 7 days.
  </p>

  <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">

  <p style="color: #6b7280; font-size: 14px; text-align: center;">
    If you didn't expect this invitation, you can safely ignore this email.
  </p>
</div>
</body>
</html>
