<?php

namespace App\Providers;

use App\Events\ApplicationStatusUpdated;
use App\Events\PaymentSucceeded;
use App\Listeners\SendApplicationStatusEmail;
use App\Listeners\SendPaymentConfirmationEmail;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ── Observer ──────────────────────────────────────────────────────
        User::observe(UserObserver::class);

        // ── Tự động tạo storage symlink nếu chưa tồn tại ─────────────────
        // Fix lỗi ảnh không hiển thị khi chưa chạy php artisan storage:link
        $storageLinkPath = public_path('storage');
        if (! file_exists($storageLinkPath) && ! is_link($storageLinkPath)) {
            try {
                \Illuminate\Support\Facades\Artisan::call('storage:link');
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('storage:link auto-create failed: ' . $e->getMessage());
            }
        }

        // ── Event → Listener ──────────────────────────────────────────────
        // Cả hai listener đều implements ShouldQueue → chạy async qua queue
        Event::listen(ApplicationStatusUpdated::class, SendApplicationStatusEmail::class);
        Event::listen(PaymentSucceeded::class, SendPaymentConfirmationEmail::class);

        // In-app notification khi payment thành công
        Event::listen(PaymentSucceeded::class, function (PaymentSucceeded $event) {
            try {
                $payment = $event->payment;
                $user    = $payment->user;
                $ends    = $payment->billing_ends
                    ? \Carbon\Carbon::parse($payment->billing_ends)->format('d/m/Y')
                    : '';
                $plan    = $payment->plan ?? 'monthly';

                app(\App\Services\NotificationService::class)
                    ->notifyPayment($user, $plan, $ends);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("notifyPayment in-app failed: " . $e->getMessage());
            }
        });
    }
}
