<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GithubController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * Redirect to GitHub OAuth với role đã được chọn trước
     */
    public function redirectWithRole(string $role): RedirectResponse
    {
        Session::put('social_preset_role', $role === 'employer' ? 'employer' : 'employee');
        return Socialite::driver('github')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $githubUser = Socialite::driver('github')->user();
        } catch (\Exception $e) {
            Log::error('GitHub OAuth callback error: ' . $e->getMessage());
            
            // CHỐT CHẶN KHỬ LOOP 429: 
            // Nếu bị lỗi kết nối với GitHub (lệch State CSRF hoặc GitHub chặn IP cũ),
            // hiển thị lỗi trực tiếp ra màn hình để debug, chặn không cho tự động redirect về trang login để tạo vòng lặp.
            dd('Ứng dụng tạm dừng để chống vòng lặp 429. Chi tiết lỗi từ GitHub/Socialite: ' . $e->getMessage() . '. Hướng giải quyết: Hãy đổi sang mạng 4G từ điện thoại để lấy IP sạch rồi bấm thử lại.');

            return redirect()->route('login')
                ->withErrors(['email' => 'Đăng nhập GitHub thất bại. Vui lòng thử lại.']);
        }

        // GitHub có thể không trả về email (private email setting)
        $email = $githubUser->getEmail();
        if (!$email) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Không thể lấy email từ GitHub. Hãy bật public email trong cài đặt GitHub.']);
        }

        // Tìm user đã tồn tại
        $user = User::where('github_id', $githubUser->getId())->first()
             ?? User::where('email', $email)->first();

        if ($user) {
            // User đã tồn tại — đăng nhập luôn
            if ($user->is_banned) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Tài khoản đã bị khóa. Vui lòng liên hệ hỗ trợ.']);
            }
            
            $user->update(array_filter([
                'github_id'         => $user->github_id ?: $githubUser->getId(),
                'profile_pic'       => $user->profile_pic ?: $githubUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?: now(),
            ]));

            Auth::login($user, true);
            return $this->redirectAfterLogin($user);
        }

        // User mới — lưu thông tin vào session để chuẩn bị đăng ký
        Session::put('social_pending', [
            'provider'   => 'github',
            'id'         => $githubUser->getId(),
            'name'       => $githubUser->getName() ?? $githubUser->getNickname(),
            'email'      => $email,
            'avatar'     => $githubUser->getAvatar(),
        ]);

        // SỬA TẠI ĐÂY: Thay vì gọi app(GithubController::class) nội bộ làm mất luồng request,
        // chúng ta thực hiện redirect HTTP thực sự sang chính route đăng ký kèm role
        $presetRole = Session::pull('social_preset_role');
        if ($presetRole) {
            return redirect()->route('auth.github.register', ['role' => $presetRole]);
        }

        return redirect()->route('auth.social.choose-role', ['provider' => 'github']);
    }

    /**
     * Xử lý sau khi user chọn role hoặc có sẵn preset role từ trước
     */
    public function completeRegistration(string $role): RedirectResponse
    {
        $pending = Session::get('social_pending');

        if (!$pending || $pending['provider'] !== 'github') {
            return redirect()->route('login')
                ->withErrors(['email' => 'Phiên đăng ký đã hết hạn hoặc không hợp lệ. Vui lòng thử lại.']);
        }

        $userType = $role === 'employer' ? 'employer' : 'employee';

        // Tạo tài khoản mới
        $user = User::create([
            'name'              => $pending['name'],
            'email'             => $pending['email'],
            'github_id'         => $pending['id'],
            'profile_pic'       => $pending['avatar'],
            'password'          => bcrypt(Str::random(32)),
            'user_type'         => $userType,
            'email_verified_at' => now(),
        ]);

        Session::forget('social_pending');

        // Gửi email chào mừng (Bọc cẩn thận để tránh nghẽn timeout ở máy local)
        try {
            Mail::to($user->email)->send(new WelcomeMail($user));
        } catch (\Exception $e) {
            Log::error('Gửi mail chào mừng GitHub thất bại ở môi trường Local: ' . $e->getMessage());
        }

        Auth::login($user, true);
        return $this->redirectAfterLogin($user);
    }

    private function redirectAfterLogin(User $user): RedirectResponse
    {
    // 1. BỎ dòng dd() test cũ đi để hệ thống chạy tiếp

    // 2. Tự động chuyển hướng về trang chủ kèm thông báo success theo từng loại tài khoản
    if ($user->user_type === 'employer') {
        // Nếu là nhà tuyển dụng, đưa về trang chủ kèm thông báo dành cho Employer
        return redirect('/')->with('success', 'Chào mừng nhà tuyển dụng, ' . $user->name . '!');
    } elseif ($user->user_type === 'employee') {
        // Nếu là người tìm việc, đưa về trang chủ kèm thông báo dành cho Employee
        return redirect('/')->with('success', 'Chào mừng ứng viên, ' . $user->name . '!');
    }

    // Dự phòng nếu không khớp role nào thì cũng đưa về trang chủ luôn
    return redirect('/')->with('success', 'Chào mừng, ' . $user->name . '!');
    }
}
