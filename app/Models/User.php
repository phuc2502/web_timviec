<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'user_type', 'profile_pic', 'resume', 'about',
        'company_name', 'company_logo', 'company_website', 'company_size', 'plan',
        'billing_ends', 'user_trial', 'is_banned', 'is_admin', 'banned_at',
        // Employee extended
        'skills', 'experience_years', 'desired_salary', 'location', 'job_type_pref',
        // Notification settings
        'mail', 'notify_shortlist', 'notify_app_status', 'notify_job_alert',
        'profile_reminder_sent_at',
        // OAuth
        'google_id', 'github_id',
        'last_seen_at',
    ];

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'         => 'datetime',
            'billing_ends'              => 'datetime',
            'user_trial'                => 'datetime',
            'banned_at'                 => 'datetime',
            'profile_reminder_sent_at'  => 'datetime',
            'password'                  => 'hashed',
            'is_banned'                 => 'boolean',
            'is_admin'                  => 'boolean',
            'mail'                      => 'boolean',
            'notify_shortlist'          => 'boolean',
            'notify_app_status'         => 'boolean',
            'notify_job_alert'          => 'boolean',
            'skills'                    => 'array',   // JSON cast
            'last_seen_at'              => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────

    public function listings()
    {
        return $this->hasMany(\App\Models\Listing::class);
    }

    public function appliedListings()
    {
        return $this->belongsToMany(\App\Models\Listing::class, 'listing_user')
                    ->withPivot(['shortlisted'])
                    ->withTimestamps();
    }

    public function cvData()
    {
        return $this->hasOne(\App\Models\CvData::class);
    }

    public function userToken()
    {
        return $this->hasOne(\App\Models\UserToken::class);
    }

    public function cvs()
    {
        return $this->hasMany(\App\Models\Cv::class);
    }

    public function applications()
    {
        return $this->hasMany(\App\Models\Application::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Subscription::class);
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class);
    }

    public function appNotifications()
    {
        return $this->hasMany(\App\Models\AppNotification::class);
    }

    // ─── Role Helpers ─────────────────────────────────────────────────────

    public function isCandidate(): bool { return $this->user_type === 'employee'; }
    public function isEmployer(): bool  { return $this->user_type === 'employer'; }
    public function isAdmin(): bool     { return (bool) $this->is_admin; }

    // ─── Premium Helpers ──────────────────────────────────────────────────

    public function isPremium(): bool
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('billing_ends', '>', now())
            ->exists();
    }

    public function daysLeft(): ?int
    {
        $sub = $this->subscriptions()
            ->where('status', 'active')
            ->where('billing_ends', '>', now())
            ->latest('billing_ends')
            ->first();

        if ($sub) return (int) now()->diffInDays($sub->billing_ends);

        if ($this->user_trial && now()->lt($this->user_trial)) {
            return (int) now()->diffInDays($this->user_trial);
        }

        return null;
    }

    public function monthlyPostCount(): int
    {
        return $this->listings()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
    }

    // ─── Profile Completeness (on-the-fly, không lưu DB) ─────────────────

    public function profileCompleteness(): array
    {
        return $this->user_type === 'employer'
            ? $this->employerCompleteness()
            : $this->employeeCompleteness();
    }

    private function employeeCompleteness(): array
    {
        $checks = [
            'Họ và tên'            => ! empty($this->name),
            'Xác minh email'       => ! is_null($this->email_verified_at),
            'Ảnh đại diện'         => ! empty($this->profile_pic),
            'Giới thiệu bản thân'  => ! empty($this->about),
            'Kỹ năng'              => ! empty($this->skills),
            'Năm kinh nghiệm'      => ! is_null($this->experience_years),
            'Mức lương mong muốn'  => ! empty($this->desired_salary),
            'Địa điểm'             => ! empty($this->location),
        ];
        return $this->calcCompleteness($checks);
    }

    private function employerCompleteness(): array
    {
        $checks = [
            'Tên đại diện'   => ! empty($this->name),
            'Xác minh email' => ! is_null($this->email_verified_at),
            'Tên công ty'    => ! empty($this->company_name),
            'Logo công ty'   => ! empty($this->company_logo),
            'Website'        => ! empty($this->company_website),
            'Quy mô công ty' => ! empty($this->company_size),
            'Giới thiệu'     => ! empty($this->about),
        ];
        return $this->calcCompleteness($checks);
    }

    private function calcCompleteness(array $checks): array
    {
        $total   = count($checks);
        $done    = count(array_filter($checks));
        $missing = array_keys(array_filter($checks, fn($v) => ! $v));

        return [
            'percent' => $total > 0 ? (int) round($done / $total * 100) : 0,
            'done'    => $done,
            'total'   => $total,
            'missing' => $missing,
        ];
    }

    // ─── Display Helpers ──────────────────────────────────────────────────

    /** "Nguyễn Văn A" → "NA" */
    public function initials(): string
    {
        $parts = explode(' ', trim($this->name ?? ''));
        if (count($parts) === 1) return strtoupper(mb_substr($parts[0], 0, 2));
        return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
    }

    /** Check if the user is currently online. */
    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->greaterThanOrEqualTo(now()->subMinutes(5));
    }

    /** Get the AI conversations for the user. */
    public function aiConversations(): HasMany
    {
        return $this->hasMany(AiConversation::class);
    }
}
