<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'user_type', 'profile_pic', 'resume', 'about',
        'company_name', 'company_logo', 'plan',
        'billing_ends', 'user_trial', 'is_banned',
        'last_seen_at',
    ];

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'billing_ends'      => 'datetime',
            'user_trial'        => 'datetime',
            'password'          => 'hashed',
            'is_banned'         => 'boolean',
            'last_seen_at'      => 'datetime',
        ];
    }

    /**
     * Check if the user is currently online.
     */
    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->greaterThanOrEqualTo(now()->subMinutes(5));
    }

    // ─── Relationships (trả về collection rỗng khi preview) ────────────────
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

    /**
     * CV online của ứng viên (1-1).
     */
    public function cvData()
    {
        return $this->hasOne(\App\Models\CvData::class);
    }

    /**
     * Lượt ứng tuyển của ứng viên (1-1).
     */
    public function userToken()
    {
        return $this->hasOne(\App\Models\UserToken::class);
    }

    /**
     * CVs đã upload của ứng viên (1-n).
     */
    public function cvs()
    {
        return $this->hasMany(\App\Models\Cv::class);
    }

    /**
     * Đơn ứng tuyển của ứng viên (1-n).
     */
    public function applications()
    {
        return $this->hasMany(\App\Models\Application::class);
    }

    /**
     * Gói subscription của nhà tuyển dụng (1-n).
     */
    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Subscription::class);
    }

    /**
     * Lịch sử thanh toán (1-n).
     */
    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class);
    }

    /**
     * Kiểm tra có phải ứng viên không.
     */
    public function isCandidate(): bool
    {
        return $this->user_type === 'employee';
    }

    /**
     * Kiểm tra có phải nhà tuyển dụng không.
     */
    public function isEmployer(): bool
    {
        return $this->user_type === 'employer';
    }
}
