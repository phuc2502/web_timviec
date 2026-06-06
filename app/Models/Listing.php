<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Listing extends Model
{
    protected $guarded = [];

    protected $casts = [
        'application_close_date' => 'datetime',
    ];

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
}
