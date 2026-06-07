<?php

namespace App\Services;

use App\Mail\ApplicationStatusMail;
use App\Mail\ShortlistMail;
use App\Mail\WelcomeMail;
use App\Mail\JobAlertMail;
use App\Models\AppNotification;
use App\Models\Application;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * NotificationService
 *
 * Điểm duy nhất để phát thông báo trong toàn hệ thống.
 *
 * Quy tắc trạng thái ứng tuyển:
 * - 'reviewing'    → chỉ in-app, KHÔNG gửi email
 * - 'interviewing' → in-app + email
 * - 'accepted'     → in-app + email
 * - 'rejected'     → in-app + email
 *
 * Email gửi async qua queue → không block request.
 * Thực tế email được dispatch qua Event → Listener (SendApplicationStatusEmail).
 */
class NotificationService
{
    // ─── 1. SHORTLIST ────────────────────────────────────────────────────────

    public function notifyShortlisted(User $applicant, Listing $listing): void
    {
        $company = $listing->user->company_name ?? $listing->user->name;

        // In-app luôn ghi, không phụ thuộc toggle email
        $this->create($applicant->id, 'shortlisted', [
            'title' => '🎉 Bạn đã được shortlist!',
            'body'  => "Nhà tuyển dụng {$company} đã shortlist hồ sơ của bạn cho vị trí \"{$listing->title}\".",
            'data'  => ['listing_id' => $listing->id, 'listing_title' => $listing->title],
        ]);

        // Email chỉ gửi nếu bật cả mail lẫn notify_shortlist
        if ($applicant->mail && $applicant->notify_shortlist) {
            try {
                Mail::to($applicant->email)->queue(new ShortlistMail($applicant, $listing));
            } catch (\Exception $e) {
                Log::error("ShortlistMail queue failed for user #{$applicant->id}: " . $e->getMessage());
            }
        }
    }

    // ─── 2. APPLICATION STATUS CHANGE ─────────────────────────────────────

    /**
     * Ghi in-app notification cho mọi thay đổi trạng thái.
     * Email chỉ được gửi (qua Event → Listener) với: interviewing, accepted, rejected.
     * Trạng thái 'reviewing' chỉ in-app.
     */
    public function notifyApplicationStatus(Application $application, string $oldStatus): void
    {
        $applicant = $application->user;
        $listing   = $application->listing;
        $company   = $listing->user->company_name ?? $listing->user->name;

        $messages = [
            'reviewing'    => ['title' => '📋 Hồ sơ đang được xem xét',    'body' => "Đơn ứng tuyển vị trí \"{$listing->title}\" tại {$company} đang được xem xét."],
            'interviewing' => ['title' => '🎯 Thư mời phỏng vấn!',          'body' => "{$company} đã mời bạn phỏng vấn cho vị trí \"{$listing->title}\". Kiểm tra email để xem chi tiết."],
            'accepted'     => ['title' => '✅ Chúc mừng! Bạn đã được nhận', 'body' => "Bạn đã được nhận vào vị trí \"{$listing->title}\" tại {$company}. Kiểm tra email để xem chi tiết."],
            'rejected'     => ['title' => '📩 Kết quả ứng tuyển',           'body' => "{$company} đã gửi thông báo kết quả ứng tuyển vị trí \"{$listing->title}\". Vui lòng kiểm tra email."],
        ];

        $msg = $messages[$application->status] ?? null;
        if (! $msg) return;

        // In-app luôn ghi cho mọi trạng thái
        $this->create($applicant->id, 'application_status', [
            'title' => $msg['title'],
            'body'  => $msg['body'],
            'data'  => [
                'application_id' => $application->id,
                'listing_id'     => $listing->id,
                'status'         => $application->status,
                'old_status'     => $oldStatus,
            ],
        ]);

        // Email async cho interviewing / accepted / rejected
        // Thực hiện qua ApplicationStatusUpdated Event → SendApplicationStatusEmail Listener
        // (được dispatch trong ApplicationService::updateStatus)
        // 'reviewing' → KHÔNG gửi email, chỉ in-app ở trên
    }

    // ─── 3. NEW APPLICATION (thông báo cho employer) ────────────────────────

    public function notifyNewApplication(Application $application): void
    {
        $employer  = $application->listing->user;
        $applicant = $application->user;
        $listing   = $application->listing;

        $this->create($employer->id, 'new_application', [
            'title' => '📥 Có ứng viên mới!',
            'body'  => "{$applicant->name} vừa ứng tuyển vào vị trí \"{$listing->title}\".",
            'data'  => [
                'application_id' => $application->id,
                'listing_id'     => $listing->id,
                'applicant_name' => $applicant->name,
            ],
        ]);

        if ($employer->mail) {
            try {
                Mail::to($employer->email)->queue(new \App\Mail\NewApplicationMail($application));
            } catch (\Exception $e) {
                Log::error("NewApplicationMail queue failed: " . $e->getMessage());
            }
        }
    }

    // ─── 4. PAYMENT SUCCESS ───────────────────────────────────────────────

    public function notifyPayment(User $user, string $plan, string $billingEnds): void
    {
        $planLabel = $plan === 'monthly' ? 'Gói Tháng' : 'Gói Năm';

        // In-app luôn ghi
        $this->create($user->id, 'payment', [
            'title' => '💳 Thanh toán thành công',
            'body'  => "Bạn đã kích hoạt {$planLabel} thành công. Hiệu lực đến {$billingEnds}.",
            'data'  => ['plan' => $plan, 'billing_ends' => $billingEnds],
        ]);

        // Email gửi qua listener SendPaymentConfirmationEmail (ShouldQueue)
        // → không gửi lại ở đây để tránh duplicate
    }

    // ─── 5. WELCOME EMAIL ────────────────────────────────────────────────

    public function sendWelcome(User $user): void
    {
        try {
            Mail::to($user->email)->queue(new WelcomeMail($user));
        } catch (\Exception $e) {
            Log::error("WelcomeMail queue failed for user #{$user->id}: " . $e->getMessage());
        }
    }

    // ─── 6. PROFILE INCOMPLETE REMINDER (gửi 1 lần sau 3 ngày) ──────────

    public function sendProfileReminder(User $user): void
    {
        // Chỉ gửi nếu chưa từng gửi
        if (! is_null($user->profile_reminder_sent_at)) return;

        $completeness = $user->profileCompleteness();
        if ($completeness['percent'] >= 100) return;

        $missing = implode(', ', $completeness['missing']);

        $this->create($user->id, 'profile_reminder', [
            'title' => '📝 Hồ sơ của bạn chưa hoàn thiện',
            'body'  => "Hãy bổ sung: {$missing} để tăng khả năng được tìm thấy bởi nhà tuyển dụng.",
            'data'  => ['percent' => $completeness['percent']],
        ]);

        // Đánh dấu đã gửi để tránh spam
        $user->update(['profile_reminder_sent_at' => now()]);

        // Không gửi email riêng cho reminder này — chỉ in-app
    }

    // ─── 7. JOB ALERT WEEKLY ─────────────────────────────────────────────

    public function sendJobAlert(User $employee, \Illuminate\Support\Collection $listings): void
    {
        if ($listings->isEmpty()) return;
        if (! $employee->notify_job_alert) return;

        $this->create($employee->id, 'job_alert', [
            'title' => "🔔 {$listings->count()} việc mới phù hợp với bạn",
            'body'  => "Có {$listings->count()} tin tuyển dụng mới khớp với kỹ năng của bạn trong tuần này.",
            'data'  => ['listing_ids' => $listings->pluck('id')->toArray()],
        ]);

        if ($employee->mail) {
            try {
                Mail::to($employee->email)->queue(new JobAlertMail($employee, $listings));
            } catch (\Exception $e) {
                Log::error("JobAlertMail queue failed for user #{$employee->id}: " . $e->getMessage());
            }
        }
    }

    // ─── PRIVATE HELPER ──────────────────────────────────────────────────

    private function create(int $userId, string $type, array $payload): void
    {
        try {
            AppNotification::create([
                'user_id' => $userId,
                'type'    => $type,
                'title'   => $payload['title'],
                'body'    => $payload['body'],
                'data'    => $payload['data'] ?? null,
                'read_at' => null,
            ]);
        } catch (\Exception $e) {
            Log::error("AppNotification::create failed (type={$type}, user={$userId}): " . $e->getMessage());
        }
    }
}
