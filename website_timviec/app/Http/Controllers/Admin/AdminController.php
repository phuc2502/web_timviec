<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Listing;
use App\Models\AiConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminController extends Controller implements HasMiddleware
{
    /**
     * Khởi tạo và bảo vệ: Chỉ có Admin mới được gọi các action trong Controller này.
     */
    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                if (auth()->user()->user_type !== 'admin') {
                    abort(403, 'Bạn không có quyền truy cập khu vực quản trị.');
                }
                return $next($request);
            }),
        ];
    }

    /**
     * GET /admin/users — Danh sách người dùng hệ thống (Phân quyền chức năng).
     */
    public function users(Request $request)
    {
        $query = User::query()->where('id', '!=', auth()->id());

        // Tìm kiếm theo tên hoặc email
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Lọc theo loại tài khoản
        if ($request->has('type') && !empty($request->type)) {
            $query->where('user_type', $request->type);
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        return view('admin.users', compact('users'));
    }

    /**
     * POST /admin/users/{id}/role — Cập nhật vai trò (Phân quyền chức năng).
     */
    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'user_type' => 'required|in:employee,employer,admin'
        ]);

        $user = User::findOrFail($id);
        $oldRole = $user->user_type;
        $user->user_type = $request->user_type;
        $user->save();

        Log::info("Admin updated role of user {$user->id} from {$oldRole} to {$user->user_type}");

        return back()->with('success', "Đã chuyển đổi thành công vai trò của {$user->name} sang " . strtoupper($request->user_type));
    }

    /**
     * POST /admin/users/{id}/plan — Cập nhật gói Premium/Trial (Phân quyền chức năng).
     */
    public function updatePlan(Request $request, $id)
    {
        $request->validate([
            'plan' => 'required|in:free,trial,premium'
        ]);

        $user = User::findOrFail($id);
        $user->plan = $request->plan;

        if ($request->plan === 'premium') {
            $user->billing_ends = now()->addDays(30);
            $user->user_trial   = null;
        } elseif ($request->plan === 'trial') {
            $user->user_trial   = now()->addDays(7);
            $user->billing_ends = null;
        } else {
            $user->billing_ends = null;
            $user->user_trial   = null;
        }

        $user->save();

        return back()->with('success', "Cập nhật gói dịch vụ cho {$user->name} thành công!");
    }

    /**
     * POST /admin/users/{id}/ban — Khóa/Mở khóa tài khoản (Phân quyền chức năng).
     */
    public function toggleBan($id)
    {
        $user = User::findOrFail($id);
        $user->is_banned = !$user->is_banned;
        $user->save();

        $status = $user->is_banned ? 'KHÓA' : 'MỞ KHÓA';
        return back()->with('success', "Đã {$status} tài khoản của {$user->name} thành công.");
    }

    /**
     * GET /admin/permissions — Quản lý phân quyền sở hữu dữ liệu (Data Authorization).
     */
    public function permissions(Request $request)
    {
        $query = Listing::query()->with('user');

        if ($request->has('search') && !empty($request->search)) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $listings = $query->latest()->paginate(10)->withQueryString();
        
        // Lấy danh sách toàn bộ Employers để hiển thị trong Select box chọn chủ mới
        $employers = User::where('user_type', 'employer')->get();

        return view('admin.permissions', compact('listings', 'employers'));
    }

    /**
     * POST /admin/permissions/transfer/{id} — Chuyển giao quyền sở hữu dữ liệu (Data Authorization).
     */
    public function transferOwnership(Request $request, $id)
    {
        $request->validate([
            'new_owner_id' => 'required|exists:users,id'
        ]);

        $listing = Listing::findOrFail($id);
        $newOwner = User::findOrFail($request->new_owner_id);

        if ($newOwner->user_type !== 'employer') {
            return back()->with('error', 'Chỉ có thể chuyển giao sở hữu tin tuyển dụng cho người dùng loại Nhà tuyển dụng.');
        }

        $oldOwnerName = $listing->user->name;
        $listing->user_id = $newOwner->id;
        $listing->save();

        Log::info("Data Ownership Transfer: Job listing '{$listing->title}' (ID: {$listing->id}) transferred from {$oldOwnerName} to {$newOwner->name}");

        return back()->with('success', "Chuyển giao quyền sở hữu tin \"{$listing->title}\" từ [{$oldOwnerName}] sang [{$newOwner->name}] thành công!");
    }

    /**
     * GET /admin/jobs — Quản lý toàn bộ tin tuyển dụng của hệ thống.
     */
    public function jobs(Request $request)
    {
        $query = Listing::query()->with('user', 'users');

        if ($request->has('search') && !empty($request->search)) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $listings = $query->latest()->paginate(10)->withQueryString();

        return view('admin.jobs', compact('listings'));
    }

    /**
     * DELETE /admin/jobs/{id} — Xóa tin tuyển dụng.
     */
    public function deleteJob($id)
    {
        $listing = Listing::findOrFail($id);
        $title = $listing->title;
        $listing->delete();

        return back()->with('success', "Đã xóa tin tuyển dụng \"{$title}\" khỏi hệ thống.");
    }

    /**
     * GET /admin/ai-chat — Quản lý các cuộc trò chuyện với AI của toàn bộ hệ thống.
     */
    public function aiConversations(Request $request)
    {
        $query = AiConversation::query()->with('user');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('title', 'like', "%{$search}%");
            });
        }

        $conversations = $query->latest('updated_at')->paginate(10)->withQueryString();

        return view('admin.ai-chat.index', compact('conversations'));
    }

    /**
     * GET /admin/ai-chat/{id} — Xem chi tiết cuộc trò chuyện AI (Auditor Mode).
     */
    public function showAiConversation($id)
    {
        $conversation = AiConversation::with('user')->findOrFail($id);
        return view('admin.ai-chat.show', compact('conversation'));
    }

    /**
     * DELETE /admin/ai-chat/{id} — Xóa cuộc trò chuyện AI.
     */
    public function deleteAiConversation($id)
    {
        $conversation = AiConversation::findOrFail($id);
        $title = $conversation->title ?? 'Không có tiêu đề';
        $userName = $conversation->user->name ?? 'Người dùng';
        $conversation->delete();

        return redirect()->route('admin.ai-chat.index')->with('success', "Đã xóa cuộc trò chuyện AI \"{$title}\" của người dùng {$userName}.");
    }
}
