<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\User;

class QuotaService
{
    private const QUOTA_LIMITS = [
        'monthly' => 5,
        'yearly' => 15,
        'trial' => 5,
    ];

    /**
     * Kiểm tra NTD có thể tạo listing mới hay không.
     * Admin luôn được phép.
     */
    public function canCreateListing(User $user): bool
    {
        if ($user->is_admin) {
            return true;
        }

        // Check subscription status
        if (!$this->hasActiveSubscription($user)) {
            return false;
        }

        $currentActive = $this->getActiveListingCount($user);
        $limit = $this->getQuotaLimit($user);

        return $currentActive < $limit;
    }

    /**
     * Đếm số listings đang hoạt động (active, pending_review, scheduled).
     */
    public function getActiveListingCount(User $user): int
    {
        return Listing::where('user_id', $user->id)
            ->whereIn('status', ['active', 'pending_review', 'scheduled'])
            ->count();
    }

    /**
     * Lấy giới hạn quota theo plan của user.
     */
    public function getQuotaLimit(User $user): int
    {
        if ($user->plan) {
            return self::QUOTA_LIMITS[$user->plan] ?? 0;
        }

        if ($user->user_trial && now()->lt($user->user_trial)) {
            return self::QUOTA_LIMITS['trial'] ?? 0;
        }

        return 0;
    }

    /**
     * Kiểm tra user có subscription hợp lệ hay không.
     */
    private function hasActiveSubscription(User $user): bool
    {
        if (isset($user->status) && $user->status === 'paid') {
            return true;
        }

        // Check trial period
        if (isset($user->user_trial) && now()->lt($user->user_trial)) {
            return true;
        }

        return false;
    }
}
