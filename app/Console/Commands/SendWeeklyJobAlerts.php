<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

/**
 * Gửi Job Alert hàng tuần cho ứng viên có skills.
 * Chạy mỗi thứ Hai lúc 8:00 sáng qua scheduler.
 */
class SendWeeklyJobAlerts extends Command
{
    protected $signature   = 'notifications:job-alerts';
    protected $description = 'Gửi job alert hàng tuần cho ứng viên dựa theo skills[]';

    public function handle(NotificationService $notif): int
    {
        // Lấy các listing mới trong 7 ngày qua
        $newListings = Listing::with('user')
            ->where('created_at', '>=', now()->subWeek())
            ->where('status', 'active')
            ->get();

        if ($newListings->isEmpty()) {
            $this->info('Không có tin mới trong tuần.');
            return 0;
        }

        // Chỉ gửi cho employee có skills và bật job alert
        $employees = User::where('user_type', 'employee')
            ->where('notify_job_alert', true)
            ->whereNotNull('skills')
            ->cursor();

        $count = 0;
        foreach ($employees as $employee) {
            $skills = $employee->skills ?? [];
            if (empty($skills)) continue;

            // Match listings theo title/description chứa bất kỳ skill nào
            $matched = $newListings->filter(function (Listing $listing) use ($skills) {
                $haystack = strtolower($listing->title . ' ' . ($listing->description ?? ''));
                foreach ($skills as $skill) {
                    if (str_contains($haystack, strtolower($skill))) return true;
                }
                return false;
            });

            if ($matched->isNotEmpty()) {
                $notif->sendJobAlert($employee, $matched);
                $count++;
            }
        }

        $this->info("Đã gửi job alert cho {$count} ứng viên.");
        return 0;
    }
}
