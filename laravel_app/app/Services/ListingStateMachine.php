<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\ListingAuditLog;
use App\Events\ListingStatusChanged;
use App\Exceptions\InvalidStateTransitionException;

class ListingStateMachine
{
    private const TRANSITIONS = [
        'draft' => ['pending_review', 'closed'],
        'pending_review' => ['active', 'rejected'],
        'scheduled' => ['pending_review', 'closed'],
        'active' => ['paused', 'closed', 'expired', 'pending_review'],
        'paused' => ['active', 'closed'],
        'expired' => ['pending_review'],
        'closed' => ['pending_review'],
        'rejected' => ['archived'],
        'archived' => []
    ];

    public function canTransition(Listing $listing, string $toStatus): bool
    {
        $allowedTransitions = self::TRANSITIONS[$listing->status] ?? [];
        return in_array($toStatus, $allowedTransitions);
    }

    public function transition(Listing $listing, string $toStatus, ?string $reason = null): bool
    {
        if (!$this->canTransition($listing, $toStatus)) {
            throw new InvalidStateTransitionException(
                "Cannot transition from {$listing->status} to {$toStatus}"
            );
        }

        $oldStatus = $listing->status;
        $listing->status = $toStatus;

        // Set rejection fields
        if ($toStatus === 'rejected') {
            $listing->rejection_reason = $reason;
            $listing->rejected_at = now();
        }

        $listing->save();

        // Log audit
        ListingAuditLog::create([
            'listing_id' => $listing->id,
            'user_id' => auth()->id() ?? 0,
            'action' => 'status_changed',
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $toStatus],
            'note' => $reason
        ]);

        // Trigger events
        event(new ListingStatusChanged($listing, $oldStatus, $toStatus));

        return true;
    }
}
