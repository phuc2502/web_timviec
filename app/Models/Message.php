<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'read_at',
        'attachment_path',
        'attachment_name',
        'email_notified'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'email_notified' => 'boolean',
    ];

    /**
     * Get the interview invitation associated with the message.
     */
    public function interviewInvitation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(InterviewInvitation::class, 'message_id');
    }

    /**
     * Get the conversation that owns the message.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    /**
     * Get the sender of the message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Mark the message as read if it is not already.
     */
    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }
}
