<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $verificationUrl;

    public function __construct(User $user, string $verificationUrl)
    {
        $this->user = $user;
        $this->verificationUrl = $verificationUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            'Verify Your Email Address - ' . config('app.name')
        );
    }

    public function content(): Content
    {
        return new Content(
            'emails.email-verification',
            'emails.email-verification-text',
            [
                'user' => $this->user,
                'verificationUrl' => $this->verificationUrl,
                'appName' => config('app.name', 'VendorConnect'),
            ]
        );
    }
}
