<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use App\Mail\PaymentConfirmationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendPaymentConfirmationEmail
{
    public function handle(PaymentSucceeded $event): void
    {
        $user = $event->payment->user;

        try {
            Mail::to($user->email)->send(new PaymentConfirmationMail($event->payment));
        } catch (\Throwable $e) {
            Log::error("SendPaymentConfirmationEmail: Gửi mail thất bại cho user {$user->id}: " . $e->getMessage());
        }
    }
}
