Reset Your Password - {{ $appName }}

Hello {{ $user->first_name }},

We received a request to reset your password for your {{ $appName }} account. If you made this request, click the link below to reset your password:

{{ $resetUrl }}

This link will expire in 60 minutes for security reasons.

If you didn't request this password reset, please ignore this email.

If you're having trouble clicking the link, copy and paste the URL above into your web browser.

---
This email was sent from {{ $appName }}. If you have any questions, please contact our support team.

© {{ date('Y') }} {{ $appName }}. All rights reserved.
