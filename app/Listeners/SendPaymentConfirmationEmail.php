<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use App\Mail\PaymentConfirmationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Gửi email xác nhận thanh toán thành công.
 */
class SendPaymentConfirmationEmail
{
    public function handle(PaymentSucceeded $event): void
    {
        $user = $event->payment->user;

        try {
            Mail::to($user->email)->send(new PaymentConfirmationMail($event->payment));

            Log::info("PaymentConfirmationMail sent to {$user->email} for payment #{$event->payment->id}");
        } catch (\Throwable $e) {
            Log::error("SendPaymentConfirmationEmail failed for user {$user->id}: " . $e->getMessage());
        }
    }
}