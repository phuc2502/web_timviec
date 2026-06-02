<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $fillable = [
        'user_id', 'listing_id', 'cv_id', 'cover_letter',
        'status', 'applied_at', 'status_updated_at',
    ];

    protected $casts = [
        'applied_at'        => 'datetime',
        'status_updated_at' => 'datetime',
    ];

    /**
     * Quy tắc chuyển trạng thái một chiều.
     * Key = trạng thái hiện tại => Value = danh sách trạng thái có thể chuyển tới
     */
    public const STATUS_TRANSITIONS = [
        'submitted'    => ['viewed', 'interviewing', 'accepted', 'rejected'],
        'viewed'       => ['interviewing', 'accepted', 'rejected'],
        'interviewing' => ['accepted', 'rejected'],
        'accepted'     => [],   // trạng thái cuối
        'rejected'     => [],   // trạng thái cuối
    ];

    public const STATUS_LABELS = [
        'submitted'    => 'Đã nộp',
        'viewed'       => 'Đã xem',
        'interviewing' => 'Phỏng vấn',
        'accepted'     => 'Đã nhận',
        'rejected'     => 'Từ chối',
    ];

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::STATUS_TRANSITIONS[$this->status] ?? []);
    }

    // ── Relationships ─────────────────────────────────────────────────────
    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
    public function listing(): BelongsTo { return $this->belongsTo(Listing::class); }
    public function cv(): BelongsTo      { return $this->belongsTo(Cv::class); }
}
