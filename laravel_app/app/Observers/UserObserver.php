<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Listing;
use App\Models\ListingAuditLog;
use App\Services\ListingStateMachine;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Check if soft deletes are used on the User model
        $isSoftDeleted = method_exists($user, 'isForceDeleting') && !$user->isForceDeleting();

        if ($isSoftDeleted) {
            // Soft delete: pause active/pending_review/scheduled listings
            $listings = Listing::where('user_id', $user->id)
                ->whereIn('status', ['active', 'pending_review', 'scheduled'])
                ->get();

            $stateMachine = app(ListingStateMachine::class);

            foreach ($listings as $listing) {
                try {
                    $stateMachine->transition($listing, 'paused', 'Tự động tạm dừng do tài khoản người dùng bị xóa tạm thời.');
                } catch (\Exception $e) {
                    Log::error("Không thể tạm dừng tin {$listing->id} khi xóa tạm người dùng: " . $e->getMessage());
                }
            }
        } else {
            // Force delete (hard delete): archive all listings
            $this->archiveAllUserListings($user);
        }
    }

    /**
     * Handle the User "forceDeleted" event.
     */
    public function forceDeleted(User $user): void
    {
        $this->archiveAllUserListings($user);
    }

    /**
     * Archive all user listings in batches of 50.
     */
    private function archiveAllUserListings(User $user): void
    {
        Listing::where('user_id', $user->id)
            ->withTrashed()
            ->chunk(50, function ($listings) {
                foreach ($listings as $listing) {
                    try {
                        $oldStatus = $listing->status;
                        
                        $listing->status = 'archived';
                        $listing->archived_reason = 'user_deleted';
                        $listing->save();

                        // Log audit
                        ListingAuditLog::create([
                            'listing_id' => $listing->id,
                            'user_id' => 0, // System
                            'action' => 'auto_archived_user_deleted',
                            'old_values' => ['status' => $oldStatus],
                            'new_values' => ['status' => 'archived'],
                            'note' => 'Tự động lưu trữ do tài khoản người dùng bị xóa vĩnh viễn.'
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Không thể lưu trữ tin {$listing->id} khi xóa vĩnh viễn người dùng: " . $e->getMessage());
                    }
                }
            });
    }
}
