<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // ═══════════════════════════════════════════════
        // ADMIN DASHBOARD
        // ═══════════════════════════════════════════════
        if ($user->user_type === 'admin' || $user->is_admin) {

            $totalUsers        = User::count();
            $totalJobs         = Listing::count();
            $totalApplications = DB::table('listing_user')->count();

            // ── Doanh thu: tính từ bảng payments (giao dịch VNPay thành công) ──
            $totalRevenue = DB::table('payments')
                ->where('status', 'success')
                ->sum('amount');

            // ── Phân loại user ──────────────────────────────────────────────
            $totalEmployees = User::where('user_type', 'employee')->count();
            $totalEmployers = User::where('user_type', 'employer')->count();

            // ── Trạng thái tài khoản ────────────────────────────────────────
            $activeUsers = User::where('is_banned', false)->count();
            $bannedUsers = User::where('is_banned', true)->count();

            // ── Gói dịch vụ (plan enum: free | trial | premium) ─────────────
            $premiumUsers = User::where('plan', 'premium')->count();
            $trialUsers   = User::where('plan', 'trial')->count();

            // ── Thống kê đơn ứng tuyển ──────────────────────────────────────
            $totalApplicationsCount = DB::table('applications')->count();

            // ── Giao dịch mới nhất (5 giao dịch) ────────────────────────────
            $recentTransactions = DB::table('payments')
                ->join('users', 'users.id', '=', 'payments.user_id')
                ->select(
                    'payments.id',
                    'payments.amount',
                    'payments.status',
                    'payments.plan',
                    'payments.created_at',
                    'users.name as user_name',
                    'users.email as user_email'
                )
                ->orderByDesc('payments.created_at')
                ->limit(5)
                ->get();

            // ── Danh sách user mới nhất (5 người) ──────────────────────────
            $recentUsers = User::latest()->take(5)->get();

            // ── Tin tuyển dụng theo trạng thái ──────────────────────────────
            $openJobs   = Listing::where('status', 'open')->count();
            $hiddenJobs = Listing::where('status', 'hidden')->count();
            $closedJobs = Listing::where('status', 'closed')->count();

            // ── Biểu đồ: Người dùng mới theo tháng (12 tháng gần đây) ───────
            $usersByMonth = DB::table('users')
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as total')
                ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month');

            // ── Biểu đồ: Tin tuyển dụng mới theo tháng (12 tháng gần đây) ───
            $jobsByMonth = DB::table('listings')
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as total')
                ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month');

            // ── Biểu đồ: Doanh thu theo tháng (12 tháng gần đây) ────────────
            $revenueByMonth = DB::table('payments')
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as total')
                ->where('status', 'success')
                ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month');

            // Tạo nhãn tháng đầy đủ 12 tháng (điền 0 nếu không có dữ liệu)
            $chartLabels     = [];
            $chartUsers      = [];
            $chartJobs       = [];
            $chartRevenue    = [];
            for ($i = 11; $i >= 0; $i--) {
                $key = now()->subMonths($i)->format('Y-m');
                $label = now()->subMonths($i)->translatedFormat('M/Y');
                // fallback nếu không có locale
                $label = now()->subMonths($i)->format('m/Y');
                $chartLabels[]  = $label;
                $chartUsers[]   = $usersByMonth[$key] ?? 0;
                $chartJobs[]    = $jobsByMonth[$key] ?? 0;
                $chartRevenue[] = (int)($revenueByMonth[$key] ?? 0);
            }

            // ── Biểu đồ tròn: phân loại gói dịch vụ ─────────────────────────
            $freeUsers = User::where('plan', 'free')->orWhereNull('plan')->count();

            return view('admin.index', compact(
                'totalUsers', 'totalJobs', 'totalApplications', 'totalRevenue',
                'totalEmployees', 'totalEmployers', 'recentUsers',
                'activeUsers', 'bannedUsers', 'premiumUsers', 'trialUsers',
                'totalApplicationsCount', 'recentTransactions',
                'openJobs', 'hiddenJobs', 'closedJobs',
                'chartLabels', 'chartUsers', 'chartJobs', 'chartRevenue',
                'freeUsers'
            ));
        }

        // ═══════════════════════════════════════════════
        // EMPLOYER DASHBOARD
        // ═══════════════════════════════════════════════
        if ($user->user_type === 'employer') {

            $totalJobs = Listing::where('user_id', $user->id)->count();

            $totalApplicants = DB::table('listing_user')
                ->join('listings', 'listings.id', '=', 'listing_user.listing_id')
                ->where('listings.user_id', $user->id)
                ->count();

            $shortlisted = DB::table('listing_user')
                ->join('listings', 'listings.id', '=', 'listing_user.listing_id')
                ->where('listings.user_id', $user->id)
                ->where('listing_user.shortlisted', true)
                ->count();

            $activeJobs = Listing::where('user_id', $user->id)
                ->where(function ($q) {
                    $q->where('application_close_date', '>=', now())
                      ->orWhereNull('application_close_date');
                })->count();

            $recentJobs = Listing::with('users')
                ->where('user_id', $user->id)
                ->latest()
                ->take(4)
                ->get();

            return view('dashboard.employer', compact(
                'totalJobs', 'totalApplicants', 'shortlisted', 'activeJobs', 'recentJobs'
            ));
        }

        // ═══════════════════════════════════════════════
        // EMPLOYEE DASHBOARD
        // ═══════════════════════════════════════════════
        $appliedJobs = $user->appliedListings()
            ->latest('listing_user.created_at')
            ->take(5)
            ->get();

        return view('dashboard.employee', compact('appliedJobs'));
    }
}
