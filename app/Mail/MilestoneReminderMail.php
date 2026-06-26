<?php

namespace App\Mail;

use App\Models\Milestone;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class MilestoneReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, Milestone>  $overdue
     * @param  Collection<int, Milestone>  $dueSoon
     */
    public function __construct(
        public readonly User $recipient,
        public readonly Collection $overdue,
        public readonly Collection $dueSoon,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name').' — Milestone reminder',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.milestone-reminder',
        );
    }
}
