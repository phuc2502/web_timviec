<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UnreadMessagesNotification extends Mailable
{
    use Queueable, SerializesModels;

    public User $recipient;
    public array $unreadMessagesData;

    /**
     * Create a new message instance.
     */
    public function __construct(User $recipient, array $unreadMessagesData)
    {
        $this->recipient = $recipient;
        $this->unreadMessagesData = $unreadMessagesData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔔 Bạn có tin nhắn tuyển dụng mới chưa đọc - Tim Viec',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.unread_messages',
        );
    }
}
