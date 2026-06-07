<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'type', 'amount', 'status',
        'vnpay_txn_ref', 'vnpay_response', 'token_amount', 'plan',
    ];

    protected $casts = ['vnpay_response' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
