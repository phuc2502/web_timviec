<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->user_type === 'admin') {
            $totalUsers        = User::count();
            $totalJobs         = Listing::count();
            $totalApplications = DB::table('listing_user')->count();
            $totalRevenue      = User::where('plan', 'premium')->count() * 490000;
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
            $totalJobs       = Listing::where('user_id', $user->id)->count();
            $totalApplicants = DB::table('listing_user')
                ->join('listings', 'listings.id', '=', 'listing_user.listing_id')
                ->where('listings.user_id', $user->id)->count();
            $shortlisted     = DB::table('listing_user')
                ->join('listings', 'listings.id', '=', 'listing_user.listing_id')
                ->where('listings.user_id', $user->id)
                ->where('listing_user.shortlisted', true)->count();
            $activeJobs      = Listing::where('user_id', $user->id)
                ->where(function ($q) {
                    $q->where('application_close_date', '>=', now())
                      ->orWhereNull('application_close_date');
                })->count();
            $recentJobs      = Listing::with('users')
                ->where('user_id', $user->id)->latest()->take(4)->get();

            return view('dashboard.employer', compact(
                'totalJobs', 'totalApplicants', 'shortlisted', 'activeJobs', 'recentJobs'
            ));
        }

        // Employee
        $appliedJobs = $user->appliedListings()
            ->latest('listing_user.created_at')->take(5)->get();

        return view('dashboard.employee', compact('appliedJobs'));
    }
}
