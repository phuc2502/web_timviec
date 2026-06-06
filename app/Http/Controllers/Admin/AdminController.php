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
    /**
     * Bảo vệ: Chỉ Admin mới được truy cập.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->user_type !== 'admin') {
                abort(403, 'Bạn không có quyền truy cập khu vực quản trị.');
            }
            return $next($request);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // USERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /admin/users — Danh sách người dùng hệ thống
     */
    public function users(Request $request)
    {
        $query = User::query()->where('id', '!=', auth()->id());

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

        $users = $query->latest()->paginate(10)->withQueryString();

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
    // PERMISSIONS (Data Authorization)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /admin/permissions — Quản lý phân quyền sở hữu dữ liệu
     */
    public function permissions(Request $request)
    {
        $query = Listing::query()->with('user');

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $listings  = $query->latest()->paginate(10)->withQueryString();
        $employers = User::where('user_type', 'employer')->get();

        return view('admin.permissions', compact('listings', 'employers'));
    }

    /**
     * POST /admin/permissions/transfer/{id} — Chuyển giao quyền sở hữu tin tuyển dụng
     */
    public function transferOwnership(Request $request, $id)
    {
        $request->validate([
            'new_owner_id' => 'required|exists:users,id',
        ]);

        $listing  = Listing::findOrFail($id);
        $newOwner = User::findOrFail($request->new_owner_id);

        if ($newOwner->user_type !== 'employer') {
            return back()->with('error', 'Chỉ có thể chuyển giao sở hữu tin tuyển dụng cho người dùng loại Nhà tuyển dụng.');
        }

        $oldOwnerName     = $listing->user->name;
        $listing->user_id = $newOwner->id;
        $listing->save();

        Log::info("Data Ownership Transfer: Job listing '{$listing->title}' (ID: {$listing->id}) transferred from {$oldOwnerName} to {$newOwner->name}");

        return back()->with('success', "Chuyển giao quyền sở hữu tin \"{$listing->title}\" từ [{$oldOwnerName}] sang [{$newOwner->name}] thành công!");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // JOBS
    // ─────────────────────────────────────────────────────────────────────────

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
            'status'                 => $job->status,
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
                $q->where('vnp_txn_ref', 'like', "%{$search}%")
                  ->orWhere('vnp_transaction_no', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('email', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan')) {
            $query->where('plan', $request->plan);
        }

        $transactions = $query->latest()->paginate(10)->withQueryString();

        return view('admin.transactions', compact('transactions'));
    }
}
