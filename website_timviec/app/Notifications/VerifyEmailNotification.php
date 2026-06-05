<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends BaseVerifyEmail
{
    use Queueable;

    /**
     * Ghi đè email xác thực để dùng template tiếng Việt.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('ITWorks — Xác thực địa chỉ email của bạn')
            ->view('emails.verify-email', [
                'notifiable'      => $notifiable,
                'verificationUrl' => $verificationUrl,
            ]);
    }
}
