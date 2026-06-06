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

            return view('admin.index', compact(
                'totalUsers', 'totalJobs', 'totalApplications', 'totalRevenue',
                'totalEmployees', 'totalEmployers', 'recentUsers',
                'activeUsers', 'bannedUsers', 'premiumUsers', 'trialUsers',
                'totalApplicationsCount', 'recentTransactions',
                'openJobs', 'hiddenJobs', 'closedJobs'
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