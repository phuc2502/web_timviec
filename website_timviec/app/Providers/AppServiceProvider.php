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

        // ── Event → Listener ──────────────────────────────────────────────
        Event::listen(ApplicationStatusUpdated::class, SendApplicationStatusEmail::class);
        Event::listen(PaymentSucceeded::class,          SendPaymentConfirmationEmail::class);
    }
}
