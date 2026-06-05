<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotification;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'google_id', 'github_id',
        'user_type', 'profile_pic', 'resume', 'about',
        'experience_years', 'desired_salary', 'location',
        'company_name', 'company_logo', 'company_website', 'company_size',
        'plan', 'billing_ends', 'user_trial',
        'is_admin', 'is_banned', 'banned_at',
        'email_verified_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'billing_ends'      => 'datetime',
            'user_trial'        => 'datetime',
            'banned_at'         => 'datetime',
            'password'          => 'hashed',
            'is_admin'          => 'boolean',
            'is_banned'         => 'boolean',
        ];
    }

    // ─── Accessors ─────────────────────────────────────────────────────────

    public function isEmployee(): bool
    {
        return $this->user_type === 'employee';
    }

    public function isEmployer(): bool
    {
        return $this->user_type === 'employer';
    }

    public function isAdmin(): bool
    {
        return $this->user_type === 'admin' || $this->is_admin;
    }

    public function onTrial(): bool
    {
        return $this->user_trial && now()->lessThan($this->user_trial);
    }

    public function hasActivePlan(): bool
    {
        return ($this->billing_ends && now()->lessThanOrEqualTo($this->billing_ends))
            || $this->onTrial();
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->profile_pic) {
            if (str_starts_with($this->profile_pic, 'http')) {
                return $this->profile_pic;
            }
            return asset('storage/images/' . $this->profile_pic);
        }

        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&background=10b981&color=fff&size=128&bold=true";
    }

    // ─── Relationships ──────────────────────────────────────────────────────

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

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }
}
