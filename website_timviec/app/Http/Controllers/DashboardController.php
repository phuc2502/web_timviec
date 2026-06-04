<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Chuyển hướng và hiển thị Dashboard động dựa theo vai trò của người dùng.
     */
    public function index()
    {
        $user = auth()->user();

        // Tự động ánh xạ Mock User từ FakeAuth sang tài khoản CSDL tương ứng để hiển thị dữ liệu thật phong phú khi test
        if ($user && ($user->email === 'user@example.com' || $user->email === 'hr@abctech.vn' || $user->id == 1)) {
            $realUser = User::where('user_type', $user->user_type)->first();
            if ($realUser) {
                $user = $realUser;
            }
        }

        if ($user->user_type === 'admin') {
            // Thống kê toàn hệ thống dành cho Admin
            $totalUsers        = User::count();
            $totalJobs         = Listing::count();
            $totalApplications = DB::table('listing_user')->count();
            $totalRevenue      = User::where('plan', 'premium')->count() * 490000; // Doanh thu giả lập từ tài khoản Premium (490K/acc)
            
            $totalEmployees    = User::where('user_type', 'employee')->count();
            $totalEmployers    = User::where('user_type', 'employer')->count();
            
            $recentUsers       = User::latest()->take(5)->get();
            $recentJobs        = Listing::with('user', 'users')->latest()->take(4)->get();

            return view('admin.index', compact(
                'totalUsers', 'totalJobs', 'totalApplications', 'totalRevenue',
                'totalEmployees', 'totalEmployers', 'recentUsers', 'recentJobs'
            ));
        }

        if ($user->user_type === 'employer') {
            // Thống kê tuyển dụng của riêng Employer
            $totalJobs        = Listing::where('user_id', $user->id)->count();
            
            $totalApplicants  = DB::table('listing_user')
                ->join('listings', 'listings.id', '=', 'listing_user.listing_id')
                ->where('listings.user_id', $user->id)
                ->count();
                
            $shortlisted      = DB::table('listing_user')
                ->join('listings', 'listings.id', '=', 'listing_user.listing_id')
                ->where('listings.user_id', $user->id)
                ->where('listing_user.shortlisted', true)
                ->count();
                
            $activeJobs       = Listing::where('user_id', $user->id)
                ->where(function($query) {
                    $query->where('application_close_date', '>=', now())
                          ->orWhereNull('application_close_date');
                })
                ->count();

            $recentJobs       = Listing::with('users')
                ->where('user_id', $user->id)
                ->latest()
                ->take(4)
                ->get();

            return view('dashboard', compact(
                'totalJobs', 'totalApplicants', 'shortlisted', 'activeJobs', 'recentJobs'
            ));
        }

        // Dashboard dành cho Ứng viên (Employee)
        $appliedJobs = $user->appliedListings()
            ->latest('listing_user.created_at')
            ->take(5)
            ->get();

        return view('dashboard', compact('appliedJobs'));
    }
}
