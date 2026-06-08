<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiConversation extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'messages',
    ];

    protected $casts = [
        'messages' => 'array',
    ];

    /**
     * Get the user that owns the AI conversation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
