<?php

namespace App\Listeners;

use App\Events\ApplicationStatusUpdated;
use App\Mail\ApplicationStatusMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendApplicationStatusEmail
{
    /** Chỉ gửi email với các trạng thái quan trọng */
    private const NOTIFY_STATUSES = ['interviewing', 'accepted', 'rejected'];

    public function handle(ApplicationStatusUpdated $event): void
    {
        if (!in_array($event->application->status, self::NOTIFY_STATUSES)) {
            return;
        }

        $candidate = $event->application->user;

        try {
            Mail::to($candidate->email)
                ->send(new ApplicationStatusMail($event->application, $event->oldStatus));
        } catch (\Throwable $e) {
            Log::error("SendApplicationStatusEmail: Gửi mail thất bại cho user {$candidate->id}: " . $e->getMessage());
        }
    }
}
