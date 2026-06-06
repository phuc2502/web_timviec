<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * GET /notifications
     *   - Có ?full=1 → trả về HTML trang đầy đủ với phân trang
     *   - Không có ?full=1 → JSON cho dropdown bell icon trên navbar
     */
    public function index(Request $request)
    {
        if ($request->boolean('full')) {
            $notifications = AppNotification::where('user_id', auth()->id())
                ->orderByDesc('created_at')
                ->paginate(20);

            return view('notifications.index', compact('notifications'));
        }

        // JSON cho AJAX (bell dropdown)
        $notifications = AppNotification::forUser(auth()->id(), 20);
        $unreadCount   = AppNotification::where('user_id', auth()->id())
                            ->whereNull('read_at')->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    /**
     * POST /notifications/{id}/read — Đánh dấu 1 thông báo đã đọc.
     */
    public function markRead(int $id)
    {
        $notif = AppNotification::where('user_id', auth()->id())->findOrFail($id);
        $notif->markRead();

        if (request()->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'Đã đánh dấu đã đọc.');
    }

    /**
     * POST /notifications/read-all — Đánh dấu tất cả đã đọc.
     */
    public function markAllRead()
    {
        AppNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (request()->wantsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'Đã đánh dấu tất cả đã đọc.');
    }
}
