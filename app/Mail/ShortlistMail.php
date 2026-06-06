<?php

namespace App\Mail;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShortlistMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $applicant,
        public readonly Listing $listing,
    ) {}

    public function envelope(): Envelope
    {
        $company = $this->listing->user->company_name ?? $this->listing->user->name;
        return new Envelope(
            subject: "🎉 [{$company}] Bạn đã được shortlist — {$this->listing->title}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.shortlist');
    }
}
