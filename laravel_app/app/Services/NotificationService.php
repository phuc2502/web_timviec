<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\User;
use App\Mail\ListingApprovedMail;
use App\Mail\ListingRejectedMail;
use App\Mail\ListingExpiryReminderMail;
use App\Mail\NewApplicationMail;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Gửi email thông báo listing đã được duyệt.
     */
    public function sendApprovalEmail(Listing $listing): void
    {
        $user = $listing->user;

        if (!$user->email_notify) {
            return;
        }

        Mail::to($user->email)->queue(new ListingApprovedMail($listing));
    }

    /**
     * Gửi email thông báo listing bị từ chối kèm lý do.
     */
    public function sendRejectionEmail(Listing $listing, string $reason): void
    {
        $user = $listing->user;

        if (!$user->email_notify) {
            return;
        }

        Mail::to($user->email)->queue(new ListingRejectedMail($listing, $reason));
    }

    /**
     * Gửi email nhắc nhở listing sắp hết hạn (3 ngày trước).
     */
    public function sendExpiryReminderEmail(Listing $listing): void
    {
        $user = $listing->user;

        if (!$user->email_notify) {
            return;
        }

        Mail::to($user->email)->queue(new ListingExpiryReminderMail($listing));
    }

    /**
     * Gửi email thông báo có ứng viên mới nộp đơn.
     */
    public function sendNewApplicationEmail(Listing $listing, User $candidate): void
    {
        $user = $listing->user;

        if (!$user->email_notify) {
            return;
        }

        Mail::to($user->email)->queue(new NewApplicationMail($listing, $candidate));
    }
}
