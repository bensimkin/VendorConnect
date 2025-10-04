Verify Your Email Address - {{ $appName }}

Hello {{ $user->first_name }},

Thank you for signing up for {{ $appName }}! To complete your registration and start using your account, please verify your email address by clicking the link below:

{{ $verificationUrl }}

This verification link will expire in 24 hours. If you don't verify your email within this time, you'll need to request a new verification email.

If you're having trouble clicking the link, copy and paste the URL above into your web browser.

If you didn't create an account with {{ $appName }}, please ignore this email.

---
This email was sent from {{ $appName }}. If you have any questions, please contact our support team.

© {{ date('Y') }} {{ $appName }}. All rights reserved.
