<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminNotificationController extends Controller
{
    protected NotificationService $notifService;

    public function __construct(NotificationService $notifService)
    {
        $this->notifService = $notifService;
    }

    /**
     * GET /admin/notifications
     * Danh sách thông báo toàn hệ thống với filter
     */
    public function index(Request $request)
    {
        $query = AppNotification::with('user')->latest();

        // Lọc theo loại
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Lọc theo trạng thái đọc
        if ($request->filled('read_status')) {
            if ($request->read_status === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->read_status === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        // Lọc theo user (search email/name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Lọc theo ngày
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $notifications = $query->paginate(20)->withQueryString();

        // Thống kê nhanh
        $stats = $this->getQuickStats();

        // Danh sách type để filter
        $types = AppNotification::select('type')
            ->distinct()
            ->pluck('type')
            ->sort()
            ->values();

        return view('admin.notifications.index', compact('notifications', 'stats', 'types'));
    }

    /**
     * GET /admin/notifications/stats (JSON)
     */
    public function stats()
    {
        return response()->json($this->getQuickStats());
    }

    /**
     * GET /admin/notifications/data (JSON — cho chart)
     */
    public function data(Request $request)
    {
        // Thông báo theo ngày (7 ngày gần nhất)
        $daily = AppNotification::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN read_at IS NOT NULL THEN 1 ELSE 0 END) as read_count')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Phân bổ theo type
        $byType = AppNotification::select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();

        return response()->json([
            'daily'   => $daily,
            'by_type' => $byType,
        ]);
    }

    /**
     * POST /admin/notifications/broadcast
     * Gửi thông báo hàng loạt
     */
    public function broadcast(Request $request)
    {
        $request->validate([
            'title'     => 'required|string|max:200',
            'body'      => 'required|string|max:1000',
            'target'    => 'required|in:all,employee,employer,specific',
            'user_id'   => 'required_if:target,specific|nullable|exists:users,id',
        ]);

        // Xác định danh sách user nhận
        $query = User::query()->where('is_banned', false);

        if ($request->target === 'employee') {
            $query->where('user_type', 'employee');
        } elseif ($request->target === 'employer') {
            $query->where('user_type', 'employer');
        } elseif ($request->target === 'specific') {
            $query->where('id', $request->user_id);
        }
        // 'all' → không filter thêm

        $users = $query->get();
        $count = 0;

        foreach ($users as $user) {
            AppNotification::create([
                'user_id' => $user->id,
                'type'    => 'admin_broadcast',
                'title'   => $request->title,
                'body'    => $request->body,
                'data'    => ['sent_by' => 'admin', 'target' => $request->target],
                'read_at' => null,
            ]);
            $count++;
        }

        return back()->with('success', "✅ Đã gửi thông báo đến {$count} người dùng thành công.");
    }

    /**
     * DELETE /admin/notifications/{id}
     */
    public function destroy(int $id)
    {
        $notif = AppNotification::findOrFail($id);
        $notif->delete();

        return back()->with('success', 'Đã xóa thông báo.');
    }

    /**
     * POST /admin/notifications/cleanup
     * Xóa thông báo cũ hàng loạt
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'older_than_days' => 'required|integer|min:1|max:365',
            'only_read'       => 'nullable|boolean',
        ]);

        $query = AppNotification::where('created_at', '<', now()->subDays($request->older_than_days));

        if ($request->boolean('only_read')) {
            $query->whereNotNull('read_at');
        }

        $deleted = $query->count();
        $query->delete();

        return back()->with('success', "🗑️ Đã xóa {$deleted} thông báo cũ.");
    }

    // ─── Private helper ───────────────────────────────────────────────────

    private function getQuickStats(): array
    {
        $total   = AppNotification::count();
        $unread  = AppNotification::whereNull('read_at')->count();
        $read    = $total - $unread;
        $today   = AppNotification::whereDate('created_at', today())->count();

        $byType  = AppNotification::select('type', DB::raw('COUNT(*) as cnt'))
            ->groupBy('type')
            ->pluck('cnt', 'type')
            ->toArray();

        return compact('total', 'unread', 'read', 'today', 'byType');
    }
}
