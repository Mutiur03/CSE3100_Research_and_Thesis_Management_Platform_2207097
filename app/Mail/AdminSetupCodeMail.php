<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminSetupCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $code,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name').' — Administrator Setup Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin-setup-code',
            with: [
                'code' => $this->code,
                'expiresMinutes' => config('setup.token_lifetime', 60),
                'setupUrl' => route('setup.complete'),
            ],
        );
    }
}
