<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    protected $fillable = [
        'employer_id', 'title', 'description', 'address',
        'job_type', 'salary', 'status', 'expire_date',
    ];

    protected $casts = [
        'expire_date' => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where(fn($q) => $q->whereNull('expire_date')
                                         ->orWhere('expire_date', '>', now()));
    }

    // ── Helpers ─────────────────────────────────────────────────────────────
    public function isExpired(): bool
    {
        return $this->expire_date && $this->expire_date->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    // ── Relationships ────────────────────────────────────────────────────────
    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
