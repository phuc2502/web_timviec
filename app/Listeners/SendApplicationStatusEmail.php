<?php

namespace App\Listeners;

use App\Events\ApplicationStatusUpdated;
use App\Mail\ApplicationStatusMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Gửi email khi trạng thái đơn chuyển sang:
 * - 'interviewing' (mời phỏng vấn)
 * - 'accepted'     (được nhận)
 * - 'rejected'     (từ chối)
 *
 * Trạng thái 'reviewing' chỉ gửi in-app notification, KHÔNG gửi email.
 * Chạy async qua queue — không block request.
 */
class SendApplicationStatusEmail implements ShouldQueue
{
    public int $tries = 3;

    public function handle(ApplicationStatusUpdated $event): void
    {
        // Chỉ gửi email cho 3 trạng thái này
        $emailStatuses = ['interviewing', 'accepted', 'rejected'];

        if (! in_array($event->application->status, $emailStatuses)) {
            return;
        }

        $candidate = $event->application->user;

        if (! $candidate->mail) {
            return; // user tắt email
        }

        try {
            Mail::to($candidate->email)
                ->queue(new ApplicationStatusMail($event->application, $event->oldStatus));

            Log::info("ApplicationStatusMail queued [{$event->application->status}] → {$candidate->email}");
        } catch (\Throwable $e) {
            Log::error("SendApplicationStatusEmail queue failed for user {$candidate->id}: " . $e->getMessage());
        }
    }
}
