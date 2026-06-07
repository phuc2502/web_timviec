<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * GET /notifications
     * - Nếu là AJAX/JSON request (từ notification bell): trả JSON
     * - Nếu là request thông thường: trả về view danh sách thông báo
     */
    public function index(Request $request)
    {
        // AJAX request từ notification bell dropdown → trả JSON
        if ($request->wantsJson() || $request->ajax()) {
            $notifications = AppNotification::forUser(auth()->id(), 20);
            $unreadCount   = AppNotification::where('user_id', auth()->id())
                                ->whereNull('read_at')->count();

            return response()->json([
                'notifications' => $notifications,
                'unread_count'  => $unreadCount,
            ]);
        }

        // Request thông thường (trang "Xem tất cả thông báo") → trả view
        $notifications = AppNotification::where('user_id', auth()->id())
                            ->orderByDesc('created_at')
                            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /** POST /notifications/{id}/read — Đánh dấu đã đọc */
    public function markRead(int $id)
    {
        $notif = AppNotification::where('user_id', auth()->id())->findOrFail($id);
        $notif->markRead();

        // Hỗ trợ cả AJAX (JSON) và form POST (redirect)
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Đã đánh dấu thông báo là đã đọc.');
    }

    /** POST /notifications/read-all — Đánh dấu tất cả đã đọc */
    public function markAllRead()
    {
        AppNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }
}
