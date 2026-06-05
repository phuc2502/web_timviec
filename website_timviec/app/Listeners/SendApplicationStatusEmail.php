<?php

namespace App\Listeners;

use App\Events\ApplicationStatusUpdated;
use App\Mail\ApplicationStatusMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendApplicationStatusEmail
{
    /**
     * Gửi email khi status = 'interviewing' HOẶC 'rejected'.
     */
    public function handle(ApplicationStatusUpdated $event): void
    {
        $emailStatuses = ['interviewing', 'rejected'];

        if (!in_array($event->application->status, $emailStatuses)) {
            return;
        }

        $candidate = $event->application->user;

        try {
            Mail::to($candidate->email)
                ->send(new ApplicationStatusMail($event->application, $event->oldStatus));

            Log::info("Status email [{$event->application->status}] sent to {$candidate->email} for application #{$event->application->id}");
        } catch (\Throwable $e) {
            Log::error("SendApplicationStatusEmail failed for user {$candidate->id}: " . $e->getMessage());
        }
    }
}
