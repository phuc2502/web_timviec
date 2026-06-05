<?php

namespace App\Observers;

use App\Models\User;
use App\Models\UserToken;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Khi ứng viên (employee) đăng ký mới → cấp 5 lượt ứng tuyển miễn phí.
     */
    public function created(User $user): void
    {
        // Chỉ cấp token cho ứng viên (employee)
        if ($user->user_type !== 'employee') {
            return;
        }

        try {
            UserToken::create([
                'user_id' => $user->id,
                'balance' => 5,
            ]);

            Log::info("UserObserver: Đã cấp 5 lượt ứng tuyển cho user {$user->id} ({$user->email})");

        } catch (\Throwable $e) {
            Log::error("UserObserver: Không thể cấp token cho user {$user->id}: " . $e->getMessage());
        }
    }
}