<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Scheduled Tasks ──────────────────────────────────────────────────────

// Nhắc hồ sơ chưa hoàn thiện — chạy mỗi ngày lúc 9 giờ sáng
Schedule::command('notifications:profile-reminders')->dailyAt('09:00');

// Job alert hàng tuần — mỗi thứ Hai lúc 8 giờ sáng
Schedule::command('notifications:job-alerts')->weeklyOn(1, '08:00');
