<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        ];
    }

    // ─── Relationships (trả về collection rỗng khi preview) ────────────────
    public function listings()
    {
        return $this->hasMany(\App\Models\Listing::class)->withDefault();
    }

    /**
     * CV online của ứng viên (1-1).
     */
    public function cvData()
    {
        return $this->hasOne(\App\Models\CvData::class);
    }
}
