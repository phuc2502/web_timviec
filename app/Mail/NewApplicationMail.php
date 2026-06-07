<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewApplicationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Application $application,
    ) {}

    public function envelope(): Envelope
    {
        $position = $this->application->listing->title;
        $applicant = $this->application->applicant_name ?? $this->application->user->name;
        return new Envelope(
            subject: "📥 [{$position}] Có ứng viên mới — {$applicant}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.new-application');
    }
}
