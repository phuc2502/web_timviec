<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Listing extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'application_close_date' => 'datetime',
    ];

    /**
     * Get the employer (user) that owns this listing.
     */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(\App\Models\Application::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'listing_user')
                    ->withPivot(['shortlisted'])
                    ->withTimestamps();
    }

    /**
     * Get the skills associated with this listing.
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'listing_skill');
    }

    /**
     * Scope a query to only include active listings.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where(function ($q1) {
                if (config('database.default') === 'sqlite') {
                    $q1->where('status', 'open')
                       ->whereDate('application_close_date', '>=', now()->toDateString());
                } else {
                    $q1->where('status', 'open')
                       ->whereDate('application_close_date', '>=', DB::raw('CURDATE()'));
                }
            })->orWhere('status', 'active');
        });
    }

    // ─── Free-tier Helpers ────────────────────────────────────────────────

    /**
     * Job của tài khoản Free đã nhận đủ 3 ứng viên chưa?
     */
    public function applicantLimitReached(): bool
    {
        if (!$this->relationLoaded('user')) {
            $this->load('user');
        }
        if ($this->user->isPremium()) {
            return false;
        }
        return $this->applications()->count() >= 3;
    }

    // ─── Status Helpers ───────────────────────────────────────────────────

    public function isPending(): bool { return ($this->status ?? 'pending') === 'pending'; }
    public function isOpen(): bool    { return $this->status === 'open'; }
    public function isHidden(): bool  { return $this->status === 'hidden'; }
    public function isClosed(): bool  { return $this->status === 'closed'; }

    public function statusLabel(): string
    {
        return match($this->status ?? 'pending') {
            'pending' => 'Chờ duyệt',
            'open'    => 'Đang mở',
            'hidden'  => 'Tạm ẩn',
            'closed'  => 'Đã đóng',
            default   => 'Không xác định',
        };
    }
}
