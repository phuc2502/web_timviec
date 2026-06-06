<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** GET /notifications — Lấy danh sách (JSON, dùng cho dropdown navbar) */
    public function index()
    {
        $notifications = AppNotification::forUser(auth()->id(), 20);
        $unreadCount   = AppNotification::where('user_id', auth()->id())
                            ->whereNull('read_at')->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    /** POST /notifications/{id}/read — Đánh dấu đã đọc */
    public function markRead(int $id)
    {
        $notif = AppNotification::where('user_id', auth()->id())->findOrFail($id);
        $notif->markRead();
        return response()->json(['ok' => true]);
    }

    /** POST /notifications/read-all — Đánh dấu tất cả đã đọc */
    public function markAllRead()
    {
        AppNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    }
}
