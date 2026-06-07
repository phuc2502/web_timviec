<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = ['user_id', 'plan', 'status', 'billing_ends'];

    protected $casts = ['billing_ends' => 'datetime'];

    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->billing_ends
            && $this->billing_ends->isFuture();
    }

    public function daysRemaining(): int
    {
        if (!$this->billing_ends || $this->billing_ends->isPast()) return 0;
        return (int) now()->diffInDays($this->billing_ends);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
