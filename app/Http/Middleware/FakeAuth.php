<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * FakeAuth — dùng để preview giao diện mà không cần đăng nhập thật.
 * Inject một user giả vào Auth guard để Blade không bị lỗi.
 */
class FakeAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Chọn loại user dựa vào query ?type=employer hoặc mặc định employee
        $type = $request->query('type', session('preview_type', 'employer'));
        session(['preview_type' => $type]);

        // Tạo user giả
        $fakeUser = new \App\Models\User();
        $fakeUser->forceFill([
            'id'               => 1,
            'name'             => $type === 'employer' ? 'ABC Tech Vietnam' : 'Nguyễn Văn A',
            'email'            => $type === 'employer' ? 'hr@abctech.vn' : 'user@example.com',
            'user_type'        => $type,
            'profile_pic'      => null,
            'resume'           => 'cv_preview.pdf',
            'about'            => 'Lập trình viên với 3 năm kinh nghiệm Laravel & Vue.js',
            'company_name'     => $type === 'employer' ? 'ABC Tech Vietnam' : null,
            'company_logo'     => null,
            'plan'             => 'monthly',
            'billing_ends'     => now()->addDays(20)->toDateTimeString(),
            'user_trial'       => now()->addDays(10)->toDateTimeString(),
            'is_banned'        => false,
            'mail'                    => true,
            'notify_shortlist'        => true,
            'notify_app_status'       => true,
            'notify_job_alert'        => true,
            'profile_reminder_sent_at'=> null,
            'skills'                  => $type === 'employee' ? ['Laravel', 'Vue.js', 'MySQL'] : null,
            'job_type_pref'           => $type === 'employee' ? 'full-time' : null,
            'experience_years'        => $type === 'employee' ? 3 : null,
            'desired_salary'          => $type === 'employee' ? 15000000 : null,
            'location'                => $type === 'employee' ? 'Hà Nội' : null,
            'email_verified_at'       => now()->toDateTimeString(),
            'created_at'       => now()->subDays(30)->toDateTimeString(),
            'password'         => bcrypt('password'),
        ]);

        Auth::setUser($fakeUser);

        return $next($request);
    }
}
