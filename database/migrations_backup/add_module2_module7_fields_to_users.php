<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module 2 & Module 7 — Thêm các cột còn thiếu vào bảng users:
 *   - skills, job_type_pref            → Employee profile
 *   - mail, notify_*, profile_reminder → Notification settings
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ── Employee Profile (Module 2) ────────────────────────────
            if (! Schema::hasColumn('users', 'skills')) {
                $table->json('skills')->nullable();
            }
            if (! Schema::hasColumn('users', 'job_type_pref')) {
                $table->string('job_type_pref', 20)->nullable();
            }

            // ── Notification Settings (Module 7) ──────────────────────
            if (! Schema::hasColumn('users', 'mail')) {
                $table->boolean('mail')->default(true);
            }
            if (! Schema::hasColumn('users', 'notify_shortlist')) {
                $table->boolean('notify_shortlist')->default(true);
            }
            if (! Schema::hasColumn('users', 'notify_app_status')) {
                $table->boolean('notify_app_status')->default(true);
            }
            if (! Schema::hasColumn('users', 'notify_job_alert')) {
                $table->boolean('notify_job_alert')->default(true);
            }
            // Dùng để tránh gửi profile reminder lặp lại
            if (! Schema::hasColumn('users', 'profile_reminder_sent_at')) {
                $table->timestamp('profile_reminder_sent_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = ['skills', 'job_type_pref', 'mail', 'notify_shortlist',
                     'notify_app_status', 'notify_job_alert', 'profile_reminder_sent_at'];
            $existing = array_filter($cols, fn($c) => Schema::hasColumn('users', $c));
            if ($existing) $table->dropColumn(array_values($existing));
        });
    }
};
