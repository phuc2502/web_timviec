<?php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use App\Mail\PaymentConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Gửi email xác nhận thanh toán — chạy async qua queue.
 */
class SendPaymentConfirmationEmail implements ShouldQueue
{
    public int $tries = 3;

    public function handle(PaymentSucceeded $event): void
    {
        $user = $event->payment->user;

        if (! $user->mail) {
            return; // user tắt email
        }

        try {
            Mail::to($user->email)->queue(new PaymentConfirmationMail($event->payment));
        } catch (\Throwable $e) {
            Log::error("SendPaymentConfirmationEmail: queue failed cho user {$user->id}: " . $e->getMessage());
        }
    }
}
