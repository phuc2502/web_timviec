<?php

namespace App\Listeners;

use App\Events\ListingStatusChanged;
use App\Mail\ListingApprovedMail;
use App\Mail\ListingRejectedMail;
use App\Mail\ListingExpiredMail;
use Illuminate\Support\Facades\Mail;

class SendListingStatusNotification
{
    public function handle(ListingStatusChanged $event): void
    {
        $listing = $event->listing;
        $user = $listing->user;

        // Check email_notify preference
        if (!$user->email_notify) {
            return;
        }

        // Send email based on status
        match($event->newStatus) {
            'active' => Mail::to($user)->queue(new ListingApprovedMail($listing)),
            'rejected' => Mail::to($user)->queue(new ListingRejectedMail($listing, $listing->rejection_reason ?? 'Vi phạm điều khoản chính sách.')),
            'expired' => Mail::to($user)->queue(new ListingExpiredMail($listing)),
            default => null
        };
    }
}
