<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('chat:notify-offline {--test : Bypass 15 minutes time check for testing}', function () {
    $testMode = $this->option('test');
    $this->info('Scanning for unread messages sent to offline users (Test mode: ' . ($testMode ? 'ON' : 'OFF') . ')...');

    $minutes = $testMode ? 0 : 15;
    $timeLimit = now()->subMinutes($minutes);

    $unreadMessages = Message::with(['conversation.employer', 'conversation.employee', 'sender'])
        ->whereNull('read_at')
        ->where('email_notified', false)
        ->where('created_at', '<=', $timeLimit)
        ->get();

    if ($unreadMessages->isEmpty()) {
        $this->info('No unread messages found.');
        return;
    }

    $notifications = [];
    $messageIdsToUpdate = [];

    foreach ($unreadMessages as $msg) {
        $conv = $msg->conversation;
        if (!$conv) continue;

        $recipientId = ($msg->sender_id === $conv->employer_id) ? $conv->employee_id : $conv->employer_id;
        $recipient = ($msg->sender_id === $conv->employer_id) ? $conv->employee : $conv->employer;

        if (!$recipient) continue;

        $isOffline = $testMode || !$recipient->last_seen_at || $recipient->last_seen_at->lessThanOrEqualTo(now()->subMinutes($minutes));

        if ($isOffline) {
            if (!isset($notifications[$recipientId])) {
                $notifications[$recipientId] = [
                    'recipient' => $recipient,
                    'messages' => [],
                ];
            }

            $notifications[$recipientId]['messages'][] = [
                'sender_name' => $msg->sender->name,
                'body' => $msg->body,
                'attachment_name' => $msg->attachment_name,
                'time' => $msg->created_at->format('H:i d/m/Y'),
            ];

            $messageIdsToUpdate[] = $msg->id;
        }
    }

    foreach ($notifications as $recipientId => $data) {
        $recipient = $data['recipient'];
        $messagesData = $data['messages'];

        try {
            Mail::to($recipient->email)->send(new \App\Mail\UnreadMessagesNotification($recipient, $messagesData));
            $this->info("Sent unread message email notification to {$recipient->name} ({$recipient->email})");
        } catch (\Throwable $e) {
            $this->error("Failed to send email to {$recipient->email}: " . $e->getMessage());
            Log::error("Failed to send offline chat notification email: " . $e->getMessage());
        }
    }

    if (!empty($messageIdsToUpdate)) {
        Message::whereIn('id', $messageIdsToUpdate)->update(['email_notified' => true]);
        $this->info('Updated ' . count($messageIdsToUpdate) . ' message status to email_notified = true.');
    }
})->purpose('Send email notifications for unread messages to offline users');

Schedule::command('chat:notify-offline')->everyFifteenMinutes();

