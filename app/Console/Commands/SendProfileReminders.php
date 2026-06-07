<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

/**
 * Gửi nhắc nhở hoàn thiện hồ sơ 1 lần duy nhất sau 3 ngày đăng ký.
 * Chạy hàng ngày qua scheduler.
 */
class SendProfileReminders extends Command
{
    protected $signature   = 'notifications:profile-reminders';
    protected $description = 'Gửi nhắc hồ sơ chưa hoàn thiện cho user đăng ký > 3 ngày';

    public function handle(NotificationService $notif): int
    {
        $users = User::whereNull('profile_reminder_sent_at')
            ->where('created_at', '<=', now()->subDays(3))
            ->cursor();

        $count = 0;
        foreach ($users as $user) {
            $notif->sendProfileReminder($user);
            $count++;
        }

        $this->info("Đã xử lý {$count} user.");
        return 0;
    }
}
