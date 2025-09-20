<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            'Welcome to ' . config('app.name')
        );
    }

    public function content(): Content
    {
        return new Content(
            'emails.welcome',
            'emails.welcome-text',
            [
                'user' => $this->user,
                'appName' => config('app.name', 'VendorConnect'),
            ]
        );
    }
}
