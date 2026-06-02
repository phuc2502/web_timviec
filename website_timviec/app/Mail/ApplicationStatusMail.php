<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Application $application,
        public readonly string $oldStatus,
    ) {}

    public function envelope(): Envelope
    {
        $label = Application::STATUS_LABELS[$this->application->status] ?? $this->application->status;
        return new Envelope(subject: "Cập nhật trạng thái ứng tuyển: {$label}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.application-status');
    }
}
