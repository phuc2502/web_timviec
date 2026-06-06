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
        $company  = $this->application->listing->user->company_name
                 ?? $this->application->listing->user->name;
        $position = $this->application->listing->title;

        $subjects = [
            'interviewing' => "🎯 [{$company}] Thư mời phỏng vấn — {$position}",
            'accepted'     => "✅ [{$company}] Chúc mừng! Bạn đã được nhận — {$position}",
            'rejected'     => "[{$company}] Thông báo kết quả ứng tuyển — {$position}",
        ];

        return new Envelope(
            subject: $subjects[$this->application->status]
                  ?? "[{$company}] Cập nhật hồ sơ ứng tuyển — {$position}"
        );
    }

    public function content(): Content
    {
        $view = match($this->application->status) {
            'interviewing' => 'emails.application-status',
            'accepted'     => 'emails.application-accepted',
            'rejected'     => 'emails.application-rejected',
            default        => 'emails.application-status',
        };

        return new Content(view: $view);
    }
}
