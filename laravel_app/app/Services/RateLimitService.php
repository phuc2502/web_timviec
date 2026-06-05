<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class RateLimitService
{
    private const RATE_LIMITS = [
        'monthly' => 5,  // 5 listings per 24h
        'yearly' => 5,   // 5 listings per 24h
        'trial' => 2,    // 2 listings per 24h
    ];

    /**
     * Kiểm tra NTD có thể tạo listing mới trong 24h hay không.
     * Admin không bị giới hạn.
     */
    public function canCreateListing(User $user): bool
    {
        if ($user->is_admin) {
            return true;
        }

        $key = $this->getCacheKey($user);
        $limit = $this->getRateLimit($user);
        $current = Cache::get($key, 0);

        return $current < $limit;
    }

    /**
     * Tăng bộ đếm rate limit sau khi tạo listing thành công.
     */
    public function incrementAttempts(User $user): void
    {
        $key = $this->getCacheKey($user);
        $ttl = 86400; // 24 hours in seconds

        if (!Cache::has($key)) {
            Cache::put($key, 1, $ttl);
        } else {
            Cache::increment($key);
        }
    }

    /**
     * Số lần tạo listing còn lại trong 24h.
     */
    public function getRemainingAttempts(User $user): int
    {
        $limit = $this->getRateLimit($user);
        $current = Cache::get($this->getCacheKey($user), 0);

        return max(0, $limit - $current);
    }

    /**
     * Thời điểm reset bộ đếm rate limit (ước tính).
     */
    public function getResetTime(User $user): string
    {
        return now()->addDay()->toIso8601String();
    }

    private function getCacheKey(User $user): string
    {
        return "rate_limit:listing:create:{$user->id}";
    }

    private function getRateLimit(User $user): int
    {
        if ($user->plan) {
            return self::RATE_LIMITS[$user->plan] ?? 0;
        }

        if ($user->user_trial && now()->lt($user->user_trial)) {
            return self::RATE_LIMITS['trial'] ?? 0;
        }

        return 0;
    }
}
