<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class JobAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $employee,
        public readonly Collection $listings,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🔔 {$this->listings->count()} việc làm mới phù hợp với bạn tuần này",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.job-alert');
    }
}
