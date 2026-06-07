<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $fillable = [
        'user_id', 'listing_id', 'cv_id', 'cover_letter',
        'status', 'apply_round', 'parent_application_id',
        'applied_at', 'status_updated_at', 'interview_scheduled_at',
        // Contact snapshot (SSOT — cả 2 phía đọc từ đây)
        'applicant_name', 'applicant_phone', 'applicant_email',
    ];

    protected $casts = [
        'applied_at'              => 'datetime',
        'status_updated_at'       => 'datetime',
        'interview_scheduled_at'  => 'datetime',
    ];

    /** Số lần ứng tuyển tối đa cho 1 job */
    public const MAX_APPLY_ROUNDS = 3;

    /**
     * State machine theo yêu cầu nghiệp vụ:
     *   submitted  → viewed, approved, interviewing, rejected
     *   viewed     → approved, interviewing, rejected
     *   approved   → interviewing, rejected   (Duyệt hồ sơ - vẫn có thể lên Phỏng vấn)
     *   interviewing → []  CLOSED (trạng thái đóng)
     *   rejected   → []   CLOSED (trạng thái đóng)
     */
    public const STATUS_TRANSITIONS = [
        'submitted'    => ['viewed', 'approved', 'interviewing', 'rejected'],
        'viewed'       => ['approved', 'interviewing', 'rejected'],
        'approved'     => ['interviewing', 'rejected'],
        'interviewing' => [],   // CLOSED
        'rejected'     => [],   // CLOSED
    ];

    public const STATUS_LABELS = [
        'submitted'    => 'Đã nộp',
        'viewed'       => 'Đã xem',
        'approved'     => 'Duyệt hồ sơ',
        'interviewing' => 'Phỏng vấn',
        'rejected'     => 'Chưa phù hợp',
    ];

    /** Trạng thái đóng - không cho phép cập nhật tiếp */
    public const CLOSED_STATUSES = ['interviewing', 'rejected'];

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::STATUS_TRANSITIONS[$this->status] ?? []);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, self::CLOSED_STATUSES);
    }

    // ── Relationships ─────────────────────────────────────────────────────
    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
    public function listing(): BelongsTo { return $this->belongsTo(Listing::class); }
    public function cv(): BelongsTo      { return $this->belongsTo(Cv::class); }

    /** Bản ghi đơn đầu tiên (lần ứng tuyển 1) */
    public function parentApplication(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'parent_application_id');
    }

    /** Các lần ứng tuyển lại (chỉ dùng từ bản ghi cha) */
    public function childApplications()
    {
        return $this->hasMany(Application::class, 'parent_application_id')
                    ->orderBy('apply_round');
    }
}
