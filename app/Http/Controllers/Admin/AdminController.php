<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // USERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /admin/users — Danh sách người dùng hệ thống
     */
    public function users(Request $request)
    {
        $query = User::query()->where('user_type', '!=', 'admin');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('user_type', $request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'banned') {
                $query->where('is_banned', true);
            } elseif ($request->status === 'active') {
                $query->where('is_banned', false);
            }
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users', compact('users'));
    }

    /**
     * POST /admin/users/{id}/role — Cập nhật vai trò
     */
    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'user_type' => 'required|in:employee,employer,admin',
        ]);

        $user    = User::findOrFail($id);
        $oldRole = $user->user_type;
        $user->user_type = $request->user_type;
        // Đồng bộ is_admin
        $user->is_admin  = ($request->user_type === 'admin') ? 1 : 0;
        $user->save();

        Log::info("Admin updated role of user {$user->id} from {$oldRole} to {$user->user_type}");

        return back()->with('success', "Đã chuyển đổi vai trò của {$user->name} sang " . strtoupper($request->user_type) . " thành công.");
    }

    /**
     * POST /admin/users/{id}/plan — Cập nhật gói Premium/Trial
     */
    public function updatePlan(Request $request, $id)
    {
        $request->validate([
            'plan' => 'required|in:free,trial,premium',
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
            // free
            $user->billing_ends = null;
            $user->user_trial   = null;
        }

        $user->save();

        return back()->with('success', "Cập nhật gói dịch vụ cho {$user->name} thành công!");
    }

    /**
     * POST /admin/users/{id}/ban — Khóa/Mở khóa tài khoản
     */
    public function toggleBan($id)
    {
        $user            = User::findOrFail($id);
        $user->is_banned = !$user->is_banned;
        $user->banned_at = $user->is_banned ? now() : null;
        $user->save();

        $status = $user->is_banned ? 'KHÓA' : 'MỞ KHÓA';

        return back()->with('success', "Đã {$status} tài khoản của {$user->name} thành công.");
    }

    /**

    /**
     * GET /admin/users/{id} — Trang chi tiết tài khoản
     */
    public function userShow($id)
    {
        $user = User::with(['listings', 'applications', 'subscriptions'])
                    ->withCount(['listings', 'applications', 'appNotifications'])
                    ->findOrFail($id);

        $recentNotifications = \App\Models\AppNotification::where('user_id', $user->id)
                                ->latest()->take(10)->get();
        $recentApplications  = $user->applications()->with('listing')->latest()->take(5)->get();
        $recentListings      = $user->listings()->latest()->take(5)->get();

        $unreadCount  = \App\Models\AppNotification::where('user_id', $user->id)
                          ->whereNull('read_at')->count();
        $completeness = $user->profileCompleteness();

        return view('admin.users.show', compact(
            'user', 'recentNotifications', 'recentApplications',
            'recentListings', 'unreadCount', 'completeness'
        ));
    }

    /**
     * POST /admin/users/{id}/notification-settings — Cập nhật cài đặt thông báo
     */
    public function updateNotificationSettings(\Illuminate\Http\Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'mail'              => $request->boolean('mail'),
            'notify_shortlist'  => $request->boolean('notify_shortlist'),
            'notify_app_status' => $request->boolean('notify_app_status'),
            'notify_job_alert'  => $request->boolean('notify_job_alert'),
        ]);
        return back()->with('success', "Đã cập nhật cài đặt thông báo cho {$user->name}.");
    }

    /**
     * DELETE /admin/users/{id} — Xóa tài khoản user (redirect to list)
     */
    public function deleteUserAndRedirect($id)
    {
        $user = User::findOrFail($id);
        if ($user->user_type === 'admin' || $user->is_admin) {
            return back()->with('error', 'Không thể xóa tài khoản Admin.');
        }
        $name = $user->name;
        $user->delete();
        Log::info("Admin deleted user ID {$id} ({$name})");
        return redirect()->route('admin.users')->with('success', "Đã xóa tài khoản của {$name} khỏi hệ thống.");
    }

    /**
     * DELETE /admin/users/{id} — Xóa tài khoản user (back)
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->user_type === 'admin' || $user->is_admin) {
            return back()->with('error', 'Không thể xóa tài khoản Admin.');
        }

        $name = $user->name;
        $user->delete();

        Log::info("Admin deleted user ID {$id} ({$name})");

        return back()->with('success', "Đã xóa tài khoản của {$name} khỏi hệ thống.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // USER DETAIL (JSON — cho modal)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /admin/users/{id}/detail
     */
    public function userDetail($id)
    {
        $user = User::withCount('listings')->findOrFail($id);

        $applicationsCount = DB::table('listing_user')
                               ->where('user_id', $user->id)
                               ->count();

        return response()->json([
            'id'                 => $user->id,
            'name'               => $user->name,
            'email'              => $user->email,
            'user_type'          => $user->user_type,
            'plan'               => $user->plan,
            'is_banned'          => (bool) $user->is_banned,
            'email_verified'     => ! is_null($user->email_verified_at),
            'created_at'         => $user->created_at->format('d/m/Y H:i'),
            'billing_ends'       => $user->billing_ends
                                        ? $user->billing_ends->format('d/m/Y')
                                        : null,
            'about'              => $user->about,
            'location'           => $user->location,
            'experience_years'   => $user->experience_years,
            'desired_salary'     => $user->desired_salary,
            'company_name'       => $user->company_name,
            'company_website'    => $user->company_website,
            'company_size'       => $user->company_size,
            'listings_count'     => $user->listings_count ?? 0,
            'applications_count' => $applicationsCount,
            'avatar_url'         => 'https://ui-avatars.com/api/?name=' . urlencode($user->name)
                                        . '&background=10b981&color=fff&size=128&bold=true',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // JOBS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /admin/jobs/pending — Danh sách tin chờ duyệt
     */
    public function pendingJobs(Request $request)
    {
        $query = Listing::query()
            ->with(['user'])
            ->where('status', 'pending')
            ->orWhereNull('status');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('company_name', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        $listings = $query->latest()->paginate(10)->withQueryString();
        $pendingCount = Listing::where('status', 'pending')->orWhereNull('status')->count();

        return view('admin.pending-jobs', compact('listings', 'pendingCount'));
    }

    /**
     * POST /admin/jobs/{id}/approve — Duyệt tin tuyển dụng
     */
    public function approveJob(Request $request, $id)
    {
        $listing = Listing::findOrFail($id);
        $listing->status = 'open';
        $listing->save();

        Log::info("Admin approved listing ID {$id}: {$listing->title}");

        return back()->with('success', "✅ Đã duyệt tin \"{$listing->title}\" thành công. Tin đang hiển thị trên hệ thống.");
    }

    /**
     * POST /admin/jobs/{id}/reject — Từ chối tin tuyển dụng
     */
    public function rejectJob(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $listing = Listing::findOrFail($id);
        $listing->status = 'hidden';
        $listing->save();

        Log::info("Admin rejected listing ID {$id}: {$listing->title}. Reason: " . ($request->reason ?? 'Không có lý do'));

        return back()->with('success', "❌ Đã từ chối tin \"{$listing->title}\". Tin bị ẩn khỏi hệ thống.");
    }

    /**
     * GET /admin/jobs
     */
    public function jobs(Request $request)
    {
        $query = Listing::query()->with(['user', 'users']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('company_name', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        $listings = $query->latest()->paginate(10)->withQueryString();

        return view('admin.jobs', compact('listings'));
    }

    /**
     * GET /admin/jobs/{id}/detail — JSON cho modal
     */
    public function jobDetail($id)
    {
        $job = Listing::with(['user', 'users'])->findOrFail($id);

        return response()->json([
            'id'                     => $job->id,
            'title'                  => $job->title,
            'slug'                   => $job->slug,
            'predes'                 => $job->predes,
            'description'            => $job->description,
            'requirements'           => $job->requirements,
            'benefits'               => $job->benefits,
            'job_type'               => $job->job_type,
            'address'                => $job->address,
            'salary'                 => $job->salary,
            'status'                 => $job->status ?? 'open',
            'feature_image'          => $job->feature_image,
            'application_close_date' => $job->application_close_date
                                            ? $job->application_close_date->format('d/m/Y')
                                            : null,
            'applicants_count'       => $job->users->count(),
            'created_at'             => $job->created_at->format('d/m/Y H:i'),
            'employer'               => [
                'id'           => $job->user->id ?? null,
                'name'         => $job->user->name ?? '—',
                'email'        => $job->user->email ?? '—',
                'company_name' => $job->user->company_name ?? $job->user->name ?? '—',
                'avatar_url'   => 'https://ui-avatars.com/api/?name=' . urlencode($job->user->name ?? 'U')
                                      . '&background=6366f1&color=fff&size=64&bold=true',
            ],
        ]);
    }

    /**
     * DELETE /admin/jobs/{id}
     */
    public function deleteJob($id)
    {
        $listing = Listing::findOrFail($id);
        $title   = $listing->title;
        $listing->delete();

        return back()->with('success', "Đã xóa tin tuyển dụng \"{$title}\" khỏi hệ thống.");
    }

    /**
     * POST /admin/jobs/{id}/status — Toggle trạng thái tin tuyển dụng
     */
    public function toggleJobStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,open,hidden,closed',
        ]);

        $listing = Listing::findOrFail($id);
        $listing->status = $request->status;
        $listing->save();

        Log::info("Admin changed status of listing ID {$id} to {$request->status}");

        return back()->with('success', "Đã cập nhật trạng thái tin \"{$listing->title}\" sang " . strtoupper($request->status) . ".");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TRANSACTIONS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /admin/transactions
     */
    public function transactions(Request $request)
    {
        $query = Transaction::query()->with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('vnpay_txn_ref', 'like', "%{$search}%")
                  ->orWhere('vnpay_response', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('email', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'paid') {
                $query->where('status', 'success');
            } else {
                $query->where('status', $status);
            }
        }

        if ($request->filled('plan')) {
            $query->where('plan', $request->plan);
        }

        $transactions = $query->latest()->paginate(10)->withQueryString();

        return view('admin.transactions', compact('transactions'));
    }
}
